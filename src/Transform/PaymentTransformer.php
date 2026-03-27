<?php

namespace Portalbox\Transform;

use InvalidArgumentException;
use Portalbox\Config;
use Portalbox\Model\UserModel;
use Portalbox\Type\Payment;

/**
 * PaymentTransformer is our bridge between dictionary representations and
 * Payment instances.
 */
class PaymentTransformer implements OutputTransformer {
	/**
	 * Called to serialize a Payment instance to a dictionary
	 *
	 * @param bool $traverse - traverse the object graph if true, otherwise
	 *      may substitute flattened representations where appropriate.
	 * @return array -  a dictionary whose values are null, string, int, float
	 *      dictionaries, or arrays with the compound types having the same
	 *      restrictions when $traverse is true or a dictionary whose values
	 *      are null, string, int, and float otherwise
	 */
	public function serialize($data, bool $traverse = false): array {
		if ($traverse) {
			return [
				'id' => $data->id(),
				'user_id' => $data->user_id(),
				'amount' => $data->amount(),
				'time' => $data->time()
			];
		} else {
			return [
				'id' => $data->id(),
				'user_id' => $data->user_id(),
				'amount' => $data->amount(),
				'time' => $data->time()
			];
		}
	}

	/**
	 * Called to get the column headers for a tabular output format eg csv.
	 * The column count should match the number of fields in an array returned
	 * by serialize() when $traverse is false
	 *
	 * @return array - a list of strings that can be column headers
	 */
	public function get_column_headers(): array {
		return ['id', 'User Id', 'Amount', 'Time'];
	}
}
