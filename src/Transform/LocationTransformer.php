<?php

namespace Portalbox\Transform;

use Portalbox\Entity\Location;

/**
 * LocationTransformer is our bridge between dictionary resprsentations and
 * Locaton entity instances.
 * 
 * @package Portalbox\Transform
 */
class LocationTransformer implements InputTransformer, OutputTransformer {
	/**
	 * validate check that the paramter is an associative array with non empty
	 * values for the 'name', 'type_id', 'mac_address', and 'location_id'
	 * keys and the presence of a timeout key then if all is well returns but if
	 * a check fails; the proper HTTP response is emitted and execution is halted.
	 */
	public function deserialize(array $data) : Location {
		return (new Location())->set_name($data['name']);
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
			$role_transformer = new RoleTransformer();
			return [
				'id' => $data->id(),
				'name' => $data->name()
			];
		} else {
			return [
				'id' => $data->id(),
				'name' => $data->name()
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
		return ['id', 'Name'];
	}
}