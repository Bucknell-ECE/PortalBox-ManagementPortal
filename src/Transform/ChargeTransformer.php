<?php

namespace Portalbox\Transform;

use InvalidArgumentException;

use Portalbox\Config;

use Portalbox\Entity\Charge;
use Portalbox\Entity\ChargePolicy;

// violation of SOLID design... should use these via interfaces and dependency injection
use Portalbox\Model\EquipmentModel;
use Portalbox\Model\UserModel;

/**
 * ChargeTransformer is our bridge between dictionary representations and
 * Charge entity instances.
 * 
 * @package Portalbox\Transform
 */
class ChargeTransformer implements InputTransformer, OutputTransformer {
	/**
	 * Deserialize a Charge entity object from a dictionary
	 * 
	 * @param array data - a dictionary representing a Charge
	 * @return Charge - a valid entity object based on the data specified
	 * @throws InvalidArgumentException if a require field is not specified
	 */
	public function deserialize(array $data) : Charge {
		if(!array_key_exists('equipment_id', $data)) {
			throw new InvalidArgumentException('\'equipment_id\' is a required field');
		}
		if(!array_key_exists('user_id', $data)) {
			throw new InvalidArgumentException('\'user_id\' is a required field');
		}
		if(!array_key_exists('amount', $data)) {
			throw new InvalidArgumentException('\'amount\' is a required field');
		}
		if(!array_key_exists('time', $data)) {
			throw new InvalidArgumentException('\'time\' is a required field');
		}
		if(!array_key_exists('charge_policy_id', $data)) {
			throw new InvalidArgumentException('\'charge_policy_id\' is a required field');
		}
		if(!ChargePolicy::is_valid($data['charge_policy_id'])) {
			throw new InvalidArgumentException('\'charge_policy_id\' must be a valid charge policy id');
		}
		if(!array_key_exists('charge_rate', $data)) {
			throw new InvalidArgumentException('\'charge_rate\' is a required field');
		}
		if(!array_key_exists('charged_time', $data)) {
			throw new InvalidArgumentException('\'charged_time\' is a required field');
		}

		$equipment = (new EquipmentModel(Config::config()))->read($data['equipment_id']);
		if(NULL === $equipment) {
			throw new InvalidArgumentException('\'equipment_id\' must correspond to a valid equipment');
		}
		$user = (new UserModel(Config::config()))->read($data['user_id']);
		if(NULL === $user) {
			throw new InvalidArgumentException('\'user_id\' must correspond to a valid user');
		}

		return (new Charge())
			->set_equipment($equipment)
			->set_user($user)
			->set_amount($data['amount'])
			->set_time($data['time'])
			->set_charge_policy_id($data['charge_policy_id'])
			->set_charge_rate($data['charge_rate'])
			->set_charged_time($data['charged_time']);
	}

	/**
	 * Called to serialize Charge entity instance to a dictionary
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
				'equipment' => $equipment_transformer->serialize($data->equipment(), $traverse),
				'user' => $user_transformer->serialize($data->user(), $traverse),
				'amount' => $data->amount(),
				'time' => $data->time(),
				'charge_policy_id' => $data->charge_policy_id(),
				'charge_rate' => $data->charge_rate(),
				'charged_time' => $data->charged_time()
			];
		} else {
			return [
				'id' => $data->id(),
				'equipment_id' => $data->equipment_id(),
				'equipment' => $data->equipment_name(),
				'user_id' => $data->user_id(),
				'user' => $data->user_name(),
				'amount' => $data->amount(),
				'time' => $data->time(),
				'charge_policy_id' => $data->charge_policy_id(),
				'charge_policy' => $data->charge_policy(),
				'charge_rate' => $data->charge_rate(),
				'charged_time' => $data->charged_time()
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
		return ['id', 'Equipment ID', 'Equipment', 'User ID', 'User', 'Amount', 'Time', 'Charge Policy ID', 'Charge Policy', 'Charge Rate', 'Charged Time'];
	}
}