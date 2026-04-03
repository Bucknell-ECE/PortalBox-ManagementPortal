<?php

namespace Portalbox\Transform;

use Portalbox\Type\Charge;

/**
 * ChargeTransformer is our bridge between dictionary representations and
 * Charge instances.
 */
class ChargeTransformer implements OutputTransformer {
	/**
	 * Called to serialize Charge instance to a dictionary
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
			'equipment_id' => $data->equipment_id(),
			'equipment' => $data->equipment_name(),
			'user_id' => $data->user_id(),
			'user' => $data->user_name(),
			'amount' => $data->amount(),
			'time' => $data->time(),
			'charge_policy' => $data->charge_policy()->value,
			'charge_rate' => $data->charge_rate(),
			'charged_time' => $data->charged_time()
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
		return ['id', 'Equipment ID', 'Equipment', 'User ID', 'User', 'Amount', 'Time', 'Charge Policy ID', 'Charge Rate', 'Charged Time'];
	}
}
