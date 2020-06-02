<?php

namespace Portalbox\Transform;

use InvalidArgumentException;

use Portalbox\Config;

use Portalbox\Entity\User;

// violation of SOLID design... should use these via interfaces and dependency injection
use Portalbox\Model\RoleModel;

/**
 * UserTransformer is our bridge between dictionary representations and User
 * entity instances.
 * 
 * @package Portalbox\Transform
 */
class UserTransformer implements InputTransformer, OutputTransformer {
	/**
	 * Deserialize a Payment entity object from a dictionary
	 * 
	 * @param array data - a dictionary representing a Payment
	 * @return Payment - a valid entity object based on the data specified
	 * @throws InvalidArgumentException if a require field is not specified
	 */
	public function deserialize(array $data) : User {
		if(!array_key_exists('role_id', $data)) {
			throw new InvalidArgumentException('\'role_id\' is a required field');
		}
		if(!array_key_exists('name', $data)) {
			throw new InvalidArgumentException('\'name\' is a required field');
		}
		if(!array_key_exists('email', $data)) {
			throw new InvalidArgumentException('\'email\' is a required field');
		}
		if(!array_key_exists('is_active', $data)) {
			throw new InvalidArgumentException('\'is_active\' is a required field');
		}

		$role = (new RoleModel(Config::config()))->read($data['role_id']);
		if(NULL === $role) {
			throw new InvalidArgumentException('\'role_id\' must correspond to a valid role');
		}

		$user = (new User())
					->set_name($data['name'])
					->set_email($data['email'])
					->set_is_active($data['is_active'])
					->set_role($role);

		// add in optional fields
		if(array_key_exists('comment', $data)) {
			$user->set_comment($data['comment']);
		}

		return $user;
	}

	/**
	 * Called to serialize a User entity instance to a dictionary
	 *
	 * @param bool $traverse - traverse the object graph if true, otherwise 
	 *      may substitute flattened representations where appropriate.
	 * @return array -  a dictionary whose values are null, string, int, float
	 *      dictionaries, or arrays with the compound types having the same
	 *      restrictions when $traverse is true or a dictionary whose values
	 *      are null, string, int, and float otherwise
	 */
	public function serialize($data, bool $traverse = false) : array {
		if($traverse) {
			$role_transformer = new RoleTransformer();
			return [
				'id' => $data->id(),
				'name' => $data->name(),
				'email' => $data->email(),
				'comment' => $data->comment(),
				'role' => $role_transformer->serialize($data->role(), $traverse),
				'is_active' => $data->is_active()
			];
		} else {
			return [
				'id' => $data->id(),
				'name' => $data->name(),
				'email' => $data->email(),
				'comment' => $data->comment(),
				'role' => $data->role_name(),
				'is_active' => $data->is_active()
			];
		}
	}

	/**
	 * Called to get the column headers for a tabular output format eg csv.
	 * The column count should mtch the number of fields in an array returned
	 * by serialize() when $traverse is false
	 * 
	 * @return array - a list of strings that ccan be column headers
	 */
	public function get_column_headers() : array {
		return ['id', 'Name', 'Email', 'Comment', 'Role', 'Active'];
	}
}