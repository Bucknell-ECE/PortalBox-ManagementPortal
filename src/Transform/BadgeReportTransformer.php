<?php

namespace Portalbox\Transform;

use Portalbox\Type\BadgeLevel;

/**
 * BadgeReportTransformer handles serializing a report of badges (BadgeLevel)
 * earned by users
 */
class BadgeReportTransformer implements OutputTransformer {
	/**
	 * Called to serialize a report row
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
			'name' => $data[0],
			'email' => $data[1],
			'badges' => implode(', ', array_map(
				fn ($badge_level) => $badge_level->name(),
				$data[2]
			))
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
		return ['Name', 'Email', 'Badges'];
	}
}
