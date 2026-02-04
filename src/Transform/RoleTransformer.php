<?php

namespace Portalbox\Transform;

use InvalidArgumentException;
use Portalbox\Type\Role;

/**
 * RoleTransformer is our bridge between dictionary representations and Role
 * instances.
 */
class RoleTransformer implements OutputTransformer {
	/**
	 * Called to serialize a Role instance to a dictionary
	 *
	 * @param bool $traverse - traverse the object graph if true, otherwise
	 *      may substitute flattened representations where appropriate.
	 * @return array -  a dictionary whose values are null, string, int, float
	 *      dictionaries, or arrays with the compound types having the same
	 *      restrictions when $traverse is true or a dictionary whose values
	 *      are null, string, int, and float otherwise
	 */
	public function serialize($data, bool $traverse = false): array {
		if ($traverse) {
			return [
				'id' => $data->id(),
				'name' => $data->name(),
				'system_role' => $data->is_system_role(),
				'description' => $data->description(),
				'permissions' => array_map(
					fn ($p) => $p->value,
					$data->permissions()
				)
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
	 * @return array - a list of strings that can be column headers
	 */
	public function get_column_headers(): array {
		return ['id', 'Name', 'System Role', 'Description'];
	}
}
