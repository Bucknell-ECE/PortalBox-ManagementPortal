<?php

namespace Portalbox\Transform;

use Portalbox\Entity\EquipmentType;

/**
 * EquipmentTypeTransformer is our bridge between dictionary resprsentations
 * and EquipmentType entity instances.
 * 
 * @package Portalbox\Transform
 */
class EquipmentTypeTransformer implements InputTransformer, OutputTransformer {
	/**
	 * TBD
	 */
	public function deserialize(array $data) : EquipmentType {
		return (new EquipmentType())
			->set_name($data['name'])
			->set_requires_training($data['requires_training'])
			->set_charge_rate($data['charge_rate'])
			->set_charge_policy_id($data['charge_policy_id']);
	}

	/**
	 * Called to serialize a Location entity instance to a dictionary
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
				'requires_training' => $data->requires_training(),
				'charge_rate' => $data->charge_rate(),
				'charge_policy_id' => $data->charge_policy_id(),
				'charge_policy' => $data->charge_policy()
			];
		} else {
			return [
				'id' => $data->id(),
				'name' => $data->name(),
				'requires_training' => $data->requires_training(),
				'charge_rate' => $data->charge_rate(),
				'charge_policy' => $data->charge_policy()
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
		return ['id', 'Name', 'Requires Training', 'Charge Rate', 'Charge Policy'];
	}
}