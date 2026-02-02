<?php

namespace Portalbox\Transform;

use InvalidArgumentException;
use Portalbox\Type\ChargePolicy;
use Portalbox\Type\EquipmentType;

/**
 * EquipmentTypeTransformer is our bridge between dictionary representations
 * and EquipmentType instances.
 */
class EquipmentTypeTransformer implements OutputTransformer {
	/**
	 * Called to serialize an EquipmentType instance to a dictionary
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
				'requires_training' => $data->requires_training(),
				'charge_rate' => $data->charge_rate(),
				'charge_policy_id' => $data->charge_policy_id(),
				'charge_policy' => $data->charge_policy(),
				'allow_proxy' => $data->allow_proxy()
			];
		} else {
			return [
				'id' => $data->id(),
				'name' => $data->name(),
				'requires_training' => $data->requires_training(),
				'charge_rate' => $data->charge_rate(),
				'charge_policy' => $data->charge_policy(),
				'allow_proxy' => $data->allow_proxy()
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
		return ['id', 'Name', 'Requires Training', 'Charge Rate', 'Charge Policy', "Allow Proxy"];
	}
}
