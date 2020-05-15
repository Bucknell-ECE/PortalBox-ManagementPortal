<?php

namespace Portalbox\Transform;

use Portalbox\Config;

use Portalbox\Entity\Equipment;

// violation of SOLID design... should use these via interfaces and dependency injection
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Model\LocationModel;

/**
 * EquipmentTransformer is our bridge between dictionary representations and
 * Equipment entity instances.
 * 
 * @package Portalbox\Transform
 */
class EquipmentTransformer implements InputTransformer, OutputTransformer {
	/**
	 * TBD
	 */
	public function deserialize(array $data) : Equipment {
		$type = (new EquipmentTypeModel(Config::config()))->read($data['type_id']);
		$location = (new LocationModel(Config::config()))->read($data['location_id']);

		return (new Equipment())
			->set_name($data['name'])
			->set_type($type)
			->set_location($location)
			->set_mac_address($data['mac_address'])
			->set_timeout($data['timeout'])
			->set_is_in_service($data['in_service']);
	}

	/**
	 * Called to serialize Equipment entity instance to a dictionary
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
				'type_id' => $data->type_id(),
				'type' => $data->type()->name(),
				'mac_address' => $data->mac_address(),
				'location_id' => $data->location_id(),
				'location' => $data->location()->name(),
				'timeout' => $data->timeout(),
				'in_service' => $data->is_in_service(),
				'in_use' => $data->is_in_use(),
				'service_minutes' => $data->service_minutes()
			];
		} else {
			return [
				'id' => $data->id(),
				'name' => $data->name(),
				'type' => $data->type()->name(),
				'mac_address' => $data->mac_address(),
				'location' => $data->location()->name(),
				'timeout' => $data->timeout(),
				'in_service' => $data->is_in_service(),
				'service_minutes' => $data->service_minutes()
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
		return ['id', 'Name', 'Type', 'MAC Address', 'Location', 'Timeout', 'In Service', 'Service Minutes'];
	}
}