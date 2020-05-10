<?php

namespace Portalbox\Transform;

use Portalbox\Entity\User;

/**
 * UserTransformer is our bridge between dictionary resprsentations and User
 * entity instances.
 * 
 * @package Portalbox\Transform
 */
class UserTransformer implements InputTransformer, OutputTransformer {
	/**
	 * validate check that the paramter is an associative array with non empty
	 * values for the 'name', 'type_id', 'mac_address', and 'location_id'
	 * keys and the presence of a timeout key then if all is well returns but if
	 * a check fails; the proper HTTP response is emitted and execution is halted.
	 */
	public function deserialize(array $data) : User {
		return new User();
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