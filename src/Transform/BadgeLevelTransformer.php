<?php

namespace Portalbox\Transform;

use Portalbox\Type\BadgeLevel;

/**
 * BadgeLevelTransformer handles serializing a BadgeLevel or list of BadgeLevels
 */
class BadgeLevelTransformer implements OutputTransformer {
	/**
	 * Called to serialize a BadgeLevel instance to a dictionary
	 *
	 * @param bool $traverse - traverse the object graph if true, otherwise
	 *      may substitute flattened representations where appropriate.
	 * @return array -  a dictionary whose values are null, string, int, float
	 *      dictionaries, or arrays with the compound types having the same
	 *      restrictions when $traverse is true or a dictionary whose values
	 *      are null, string, int, and float otherwise
	 */
	public function serialize($data, bool $traverse = false): array {
		return [
			'name' => $data->name(),
			'image' => $data->image(),
			'uses' => $data->uses()
		];
	}

	/**
	 * Called to get the column headers for a tabular output format eg csv.
	 * The column count should match the number of fields in an array returned
	 * by serialize() when $traverse is false
	 *
	 * @return array - a list of strings that can be column headers
	 */
	public function get_column_headers(): array {
		return ['Name', 'Image', 'Uses'];
	}
}
