<?php

namespace Portalbox\Transform;

use InvalidArgumentException;

use Portalbox\Entity\ChargePolicy;
use Portalbox\Entity\EquipmentType;

/**
 * EquipmentTypeTransformer is our bridge between dictionary representations
 * and EquipmentType entity instances.
 * 
 * @package Portalbox\Transform
 */
class EquipmentTypeTransformer implements InputTransformer, OutputTransformer {
	/**
	 * Deserialize an EquipmentType entity object from a dictionary
	 * 
	 * @param array data - a dictionary representing a Role
	 * @return EquipmentType - a valid entity object based on the data specified
	 * @throws InvalidArgumentException if a require field is not specified
	 */
	public function deserialize(array $data) : EquipmentType {
		if(!array_key_exists('name', $data)) {
			throw new InvalidArgumentException('\'name\' is a required field');
		}
		if(!array_key_exists('requires_training', $data)) {
			throw new InvalidArgumentException('\'requires_training\' is a required field');
		}
		if(!array_key_exists('charge_policy_id', $data)) {
			throw new InvalidArgumentException('\'charge_policy_id\' is a required field');
		}
		if(!ChargePolicy::is_valid($data['charge_policy_id'])) {
			throw new InvalidArgumentException('\'charge_policy_id\' must be a valid charge policy id');
		}
		if(!array_key_exists('charge_rate', $data)) {
			throw new InvalidArgumentException('\'charge_rate\' is a required field');
		}
		if(!array_key_exists('allow_proxy', $data)) {
			throw new InvalidArgumentException('\'allow_proxy\' is a required field');
		}


		return (new EquipmentType())
			->set_name(htmlspecialchars($data['name']))
			->set_requires_training($data['requires_training'])
			->set_charge_rate($data['charge_rate'])
			->set_charge_policy_id($data['charge_policy_id'])
			->set_allow_proxy($data['allow_proxy']);
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
	 * @return array - a list of strings that ccan be column headers
	 */
	public function get_column_headers() : array {
		return ['id', 'Name', 'Requires Training', 'Charge Rate', 'Charge Policy', "Allow Proxy"];
	}
}