<?php

namespace Portalbox\Transform;

use Portalbox\Entity\BadgeRule;

/**
 * BadgeRuleTransformer handles serializing a BadgeRule or list of BadgeRules
 */
class BadgeRuleTransformer implements OutputTransformer {
	/**
	 * Called to serialize a BadgeRule instance to a dictionary
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
			'id' => $data->id(),
			'name' => $data->name(),
			'equipment_types' => $data->equipment_type_ids()
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
		return ['id', 'Name'];
	}
}
