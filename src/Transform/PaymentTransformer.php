<?php

namespace Portalbox\Transform;

use InvalidArgumentException;

use Portalbox\Config;

use Portalbox\Entity\Payment;

// violation of SOLID design... should use these via interfaces and dependency injection
use Portalbox\Model\UserModel;

/**
 * PaymentTransformer is our bridge between dictionary representations and
 * Payment entity instances.
 * 
 * @package Portalbox\Transform
 */
class PaymentTransformer implements InputTransformer, OutputTransformer {
	/**
	 * Deserialize a Payment entity object from a dictionary
	 * 
	 * @param array data - a dictionary representing a Payment
	 * @return Payment - a valid entity object based on the data specified
	 * @throws InvalidArgumentException if a require field is not specified
	 */
	public function deserialize(array $data) : Payment {
		if(!array_key_exists('user_id', $data)) {
			throw new InvalidArgumentException('\'user_id\' is a required field');
		}
		if(!array_key_exists('amount', $data)) {
			throw new InvalidArgumentException('\'amount\' is a required field');
		}
		if(!array_key_exists('time', $data)) {
			throw new InvalidArgumentException('\'time\' is a required field');
		}

		$user = (new UserModel(Config::config()))->read($data['user_id']);
		if(NULL === $user) {
			throw new InvalidArgumentException('\'user_id\' must correspond to a valid user');
		}

		return (new Payment())
			->set_user($user)
			->set_amount($data['amount'])
			->set_time($data['time']);
	}

	/**
	 * Called to serialize a Payment entity instance to a dictionary
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
	 * @return array - a list of strings that ccan be column headers
	 */
	public function get_column_headers() : array {
		return ['id', 'User Id', 'Amount', 'Time'];
	}
}