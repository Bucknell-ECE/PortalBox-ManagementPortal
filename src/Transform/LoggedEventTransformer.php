<?php

namespace Portalbox\Transform;

use Portalbox\Config;

use Portalbox\Entity\LoggedEvent;

/**
 * LoggedEventTransformer is our bridge between dictionary representations and
 * LoggedEvent entity instances.
 * 
 * @package Portalbox\Transform
 */
class LoggedEventTransformer implements OutputTransformer {

	/**
	 * Called to serialize LoggedEvent entity instance to a dictionary
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
			$equipment_transformer = new EquipmentTransformer();
			$user_transformer = new UserTransformer();
			return [
				'id' => $data->id(),
				'time' => $data->time(),
				'type_id' => $data->type_id(),
				'card_id' => $data->card_id(),
				'user' => $user_transformer->serialize($data->user(), false),
				'equipment' => $equipment_transformer->serialize($data->equipment(), true),
			];
		} else {
			return [
				'id' => $data->id(),
				'time' => $data->time(),
				'type' =>$data->type(),
				'card' => $data->card_id(),
				'user' => $data->user_name(),
				'equipment_name' => $data->equipment_name(),
				'equipment_type' => $data->equipment_type(),
				'location' => $data->location_name()
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
		return ['id', 'Time', 'Type', 'Card', 'User', 'Equipment Name', 'Equipment Type', 'Location'];
	}
}