<?php

namespace Portalbox\Transform;

use InvalidArgumentException;

use Portalbox\Entity\Location;

/**
 * LocationTransformer is our bridge between dictionary representations and
 * Location entity instances.
 * 
 * @package Portalbox\Transform
 */
class LocationTransformer implements InputTransformer, OutputTransformer {
	/**
	 * Deserialize a Location entity object from a dictionary
	 * 
	 * @param array data - a dictionary representing a Location
	 * @return Location - a valid entity object based on the data specified
	 * @throws InvalidArgumentException if a require field is not specified
	 */
	public function deserialize(array $data) : Location {
		if(!array_key_exists('name', $data)) {
			throw new InvalidArgumentException('\'name\' is a required field');
		}

		return (new Location())->set_name(htmlspecialchars($data['name']));
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
	 * The column count should match the number of fields in an array returned
	 * by serialize() when $traverse is false
	 * 
	 * @return array - a list of strings that ccan be column headers
	 */
	public function get_column_headers() : array {
		return ['id', 'Name'];
	}
}