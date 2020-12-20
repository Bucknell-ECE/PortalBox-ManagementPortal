<?php

namespace Portalbox\Transform;

use InvalidArgumentException;
use Portalbox\Entity\Role;

/**
 * RoleTransformer is our bridge between dictionary representations and Role
 * entity instances.
 * 
 * @package Portalbox\Transform
 */
class RoleTransformer implements InputTransformer, OutputTransformer {
	/**
	 * Deserialize a Role entity object from a dictionary
	 * 
	 * @param array data - a dictionary representing a Role
	 * @return Role - a valid entity object based on the data specified
	 * @throws InvalidArgumentException if a require field is not specified
	 */
	public function deserialize(array $data) : Role {
		if(!array_key_exists('name', $data)) {
			throw new InvalidArgumentException('\'name\' is a required field');
		}
		if(!array_key_exists('description', $data)) {
			throw new InvalidArgumentException('\'description\' is a required field');
		}
		if(!array_key_exists('permissions', $data)) {
			throw new InvalidArgumentException('\'permissions\' is a required field');
		}

		return (new Role())
			->set_name(htmlspecialchars($data['name']))
			->set_description(htmlspecialchars($data['description']))
			->set_is_system_role(false)	// hard coded as a business rule
			->set_permissions($data['permissions']);
	}

	/**
	 * Called to serialize a Role entity instance to a dictionary
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
			return [
				'id' => $data->id(),
				'name' => $data->name(),
				'system_role' => $data->is_system_role(),
				'description' => $data->description(),
				'permissions' => $data->permissions()
			];
		} else {
			return [
				'id' => $data->id(),
				'name' => $data->name(),
				'system_role' => $data->is_system_role(),
				'description' => $data->description()
			];
		}
	}

	/**
	 * Called to get the column headers for a tabular output format eg csv.
	 * The column count should match the number of fields in an array returned
	 * by serialize() when $traverse is false
	 * 
	 * @return array - a list of strings that ccan be column headers
	 */
	public function get_column_headers() : array {
		return ['id', 'Name', 'System Role', 'Description'];
	}
}