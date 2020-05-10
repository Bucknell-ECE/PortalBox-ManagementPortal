<?php

namespace Portalbox\Transform;

use Portalbox\Entity\Equipment;
/**
 * EquipmentTransformer is our bridge between dictionary resprsentations and
 * Equipment entity instances.
 * 
 * @package Portalbox\Transform
 */
class EquipmentTransformer implements InputTransformer, OutputTransformer {
	/**
	 * validate check that the paramter is an associative array with non empty
	 * values for the 'name', 'type_id', 'mac_address', and 'location_id'
	 * keys and the presence of a timeout key then if all is well returns but if
	 * a check fails; the proper HTTP response is emitted and execution is halted.
	 */
	public function deserialize(array $data) : Equipment {
		$equipment = (new Equipment())
			->set_name($input['name']);

		// //->set_type($type)
		// //->set_location($location)
		// ->set_mac_address($input['mac_address'])
		// ->set_timeout($input['timeout'])
		// ->set_is_in_service($input['in_service'])
		// ->set_service_minutes($input['service_minutes']);

		return $equipment;

		// if(!array_key_exists('mac_address', $equipment) || empty($equipment['mac_address'])) {
		// 	header('HTTP/1.0 400 Bad Request');
		// 	die('You must specify the equipment\'s mac_address');
		// } else if(FALSE == preg_match('/^([0-9A-Fa-f]{2}[:-]?){5}([0-9A-Fa-f]{2})$/', $equipment['mac_address'])) {
		// 	header('HTTP/1.0 400 Bad Request');
		// 	die('You must specify a valid mac_address for the equipment eg. 00:11:22:AA:BB:CC');
		// }
		// if(!array_key_exists('location_id', $equipment) || empty($equipment['location_id'])) {
		// 	header('HTTP/1.0 400 Bad Request');
		// 	die('You must specify the equipment\'s location_id');
		// } else {
		// 	$connection = DB::getConnection();
		// 	$sql = 'SELECT id FROM locations WHERE id = :id';
		// 	$query = $connection->prepare($sql);
		// 	$query->bindValue(':id', $equipment['location_id']);
		// 	if($query->execute()) {
		// 		$location = $query->fetch(PDO::FETCH_ASSOC);
		// 		if(!$location) {
		// 			header('HTTP/1.0 400 Bad Request');
		// 			die('You must specify a valid location_id for the equipment');
		// 		}
		// 	} else {
		// 		header('HTTP/1.0 500 Internal Server Error');
		// 		die('We experienced issues communicating with the database');
		// 	}
		// }
		// if(!array_key_exists('timeout', $equipment) || 0 > intval($equipment['timeout'])) {
		// 	header('HTTP/1.0 400 Bad Request');
		// 	die('You must specify the equipment\'s timeout');
		// }
		// if(!array_key_exists('in_service', $equipment) || !isset($equipment['in_service'])) {
		// 	header('HTTP/1.0 400 Bad Request');
		// 	die('You must specify whether the equipment is in service');
		// }
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