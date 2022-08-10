<?php

namespace Portalbox\Transform;

use InvalidArgumentException;

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
	 * Deserialize a Equipment entity object from a dictionary
	 * 
	 * @param array data - a dictionary representing a Equipment
	 * @return Equipment - a valid entity object based on the data specified
	 * @throws InvalidArgumentException if a require field is not specified
	 */
	public function deserialize(array $data) : Equipment {
		if(!array_key_exists('name', $data)) {
			throw new InvalidArgumentException('\'name\' is a required field');
		}
		if(!array_key_exists('type_id', $data)) {
			throw new InvalidArgumentException('\'type_id\' is a required field');
		}
		if(!array_key_exists('location_id', $data)) {
			throw new InvalidArgumentException('\'location_id\' is a required field');
		}
		if(!array_key_exists('mac_address', $data)) {
			throw new InvalidArgumentException('\'mac_address\' is a required field');
		}
		if(!array_key_exists('timeout', $data)) {
			throw new InvalidArgumentException('\'timeout\' is a required field');
		}
		if(!array_key_exists('in_service', $data)) {
			throw new InvalidArgumentException('\'in_service\' is a required field');
		}

		$type = (new EquipmentTypeModel(Config::config()))->read($data['type_id']);
		if(NULL === $type) {
			throw new InvalidArgumentException('\'type_id\' must correspond to a valid equiment type');
		}
		$location = (new LocationModel(Config::config()))->read($data['location_id']);
		if(NULL === $location) {
			throw new InvalidArgumentException('\'location_id\' must correspond to a valid location');
		}

		return (new Equipment())
			->set_name(htmlspecialchars($data['name']))
			->set_type($type)
			->set_location($location)
			->set_mac_address($data['mac_address'])
			->set_timeout($data['timeout'])
			->set_is_in_service($data['in_service'])
			->set_service_minutes($data["service_minutes"]);
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
				'mac_address' => is_null($data->mac_address()) ? NULL :$data->mac_address(),
				'location_id' => $data->location_id(),
				'location' => $data->location()->name(),
				'timeout' => $data->timeout(),
				'in_service' => $data->is_in_service(),
				'in_use' => $data->is_in_use(),
				'service_minutes' => $data->service_minutes(),
				'ip_address' => $data->ip_address()
			];
		} else {
			return [
				'id' => $data->id(),
				'name' => $data->name(),
				'type' => $data->type()->name(),
				'mac_address' => is_null($data->mac_address()) ? '' :$data->mac_address(),
				'location' => $data->location()->name(),
				'timeout' => $data->timeout(),
				'in_service' => $data->is_in_service(),
				'in_use' => $data->is_in_use(),
				'service_minutes' => $data->service_minutes(),
				'ip_address' => $data->ip_address()
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
		return ['id', 'Name', 'Type', 'MAC Address', 'Location', 'Timeout', 'In Service', 'In Use', 'Service Minutes'];
	}
}