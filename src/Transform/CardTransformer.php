<?php

namespace Portalbox\Transform;

use InvalidArgumentException;

use Portalbox\Config;

use Portalbox\Entity\Card;
use Portalbox\Entity\CardType;
use Portalbox\Entity\ProxyCard;
use Portalbox\Entity\ShutdownCard;
use Portalbox\Entity\TrainingCard;
use Portalbox\Entity\UserCard;

// violation of SOLID design... should use these via interfaces and dependency injection
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Model\UserModel;

/**
 * CardTransformer is our bridge between dictionary representations and
 * Card entity instances.
 * 
 * @package Portalbox\Transform
 */
class CardTransformer implements InputTransformer, OutputTransformer {
	/**
	 * Deserialize a Card entity object from a dictionary
	 * 
	 * @param array data - a dictionary representing a Card
	 * @return Card - a valid entity object based on the data specified
	 * @throws InvalidArgumentException if a require field is not specified
	 */
	public function deserialize(array $data) : Card {
		if(!array_key_exists('id', $data)) {
			throw new InvalidArgumentException('\'id\' is a required field');
		}
		if(!array_key_exists('type_id', $data)) {
			throw new InvalidArgumentException('\'type_id\' is a required field');
		}

		if(CardType::USER == $data['type_id']) {
			$user = (new UserModel(Config::config()))->read($data['user_id']);
			if(NULL === $user) {
				throw new InvalidArgumentException('\'user_id\' must correspond to a valid user');
			}

			return (new UserCard())
				->set_id($data['id'])
				->set_user_id($data['user_id']);
		} else if (CardType::TRAINING == $data['type_id']) {
			$type = (new EquipmentTypeModel(Config::config()))->read($data['equipment_type_id']);
			if(NULL === $type) {
				throw new InvalidArgumentException('\'equipment_type_id\' must correspond to a valid equiment type');
			}

			return (new TrainingCard())
				->set_id($data['id'])
				->set_equipment_type_id($data['equipment_type_id']);
		} else if (CardType::PROXY == $data['type_id']) {
			return (new ProxyCard())
				->set_id($data['id']);
		} else if (CardType::SHUTDOWN == $data['type_id']) {
			return (new ShutdownCard())
				->set_id($data['id']);
		}


		throw new InvalidArgumentException('\'type_id\' must correspond to a valid card type id');
	}

	/**
	 * Called to serialize Card entity instance to a dictionary
	 *
	 * @param bool $traverse - traverse the object graph if true, otherwise 
	 *      may substitute flattened representations where appropriate.
	 * @return array -  a dictionary whose values are null, string, int, float
	 *      dictionaries, or arrays with the compound types having the same
	 *      restrictions when $traverse is true or a dictionary whose values
	 *      are null, string, int, and float otherwise
	 */
	public function serialize($data, bool $traverse = false) : array {
		$card_type_id = $data->type_id();

		if($traverse) {
			if(CardType::USER == $card_type_id) {
				$user_transformer = new UserTransformer();
				return [
					'id' => $data->id(),
					'card_type_id' => $card_type_id,
					'card_type' => CardType::name_for_type($card_type_id),
					'user' => is_null($data->user()) ? NULL : $user_transformer->serialize($data->user(), $traverse),
					'equipment_type' => NULL
				];
			} else if (CardType::TRAINING == $card_type_id) {
				$equipment_type_transformer = new EquipmentTypeTransformer();
				return [
					'id' => $data->id(),
					'card_type_id' => $card_type_id,
					'card_type' => CardType::name_for_type($card_type_id),
					'user' => NULL,
					'equipment_type' => is_null($data->equipment_type()) ? NULL : $equipment_type_transformer->serialize($data->equipment_type(), $traverse)
				];
			} else if (CardType::PROXY == $card_type_id) {
				return [
					'id' => $data->id(),
					'card_type_id' => $card_type_id,
					'card_type' => CardType::name_for_type($card_type_id),
					'user' => NULL,
					'equipment_type' => NULL
				];
			} else if (CardType::SHUTDOWN == $card_type_id) {
				return [
					'id' => $data->id(),
					'card_type_id' => $card_type_id,
					'card_type' => CardType::name_for_type($card_type_id),
					'user' => NULL,
					'equipment_type' => NULL
				];
			}
		} else {
			if(CardType::USER == $card_type_id) {
				return [
					'id' => $data->id(),
					'card_type_id' => $card_type_id,
					'card_type' => CardType::name_for_type($card_type_id),
					'user_id' => $data->user_id(),
					'user' => is_null($data->user) ? '' : $data->user->name(),
					'equipment_type_id' => '',
					'equipment_type' => ''
				];
			} else if (CardType::TRAINING == $card_type_id) {
				return [
					'id' => $data->id(),
					'card_type_id' => $card_type_id,
					'card_type' => CardType::name_for_type($card_type_id),
					'user_id' => '',
					'user' => '',
					'equipment_type_id' => $data->equipment_type_id(),
					'equipment_type' => is_null($data->equipment_type) ? '' : $data->equipment_type->name()
				];
			} else if (CardType::PROXY == $card_type_id) {
				return [
					'id' => $data->id(),
					'card_type_id' => $card_type_id,
					'card_type' => CardType::name_for_type($card_type_id),
					'user_id' => '',
					'user' => '',
					'equipment_type_id' => '',
					'equipment_type' => ''
				];
			} else if (CardType::SHUTDOWN == $card_type_id) {
				return [
					'id' => $data->id(),
					'card_type_id' => $card_type_id,
					'card_type' => CardType::name_for_type($card_type_id),
					'user_id' => '',
					'user' => '',
					'equipment_type_id' => '',
					'equipment_type' => ''
				];
			}
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
		return ['id', 'Card Type ID', 'Card Type', 'User ID', 'User', 'Equipment Type ID', 'Equipment Type'];
	}
}