<?php

namespace Portalbox\Transform;

use InvalidArgumentException;
use Portalbox\Config;
use Portalbox\Enumeration\CardType;
use Portalbox\Type\Card;

/**
 * CardTransformer is our bridge between dictionary representations and
 * Card instances.
 */
class CardTransformer implements OutputTransformer {
	/**
	 * Called to serialize Card instance to a dictionary
	 *
	 * @param bool $traverse - traverse the object graph if true, otherwise
	 *      may substitute flattened representations where appropriate.
	 * @return array -  a dictionary whose values are null, string, int, float
	 *      dictionaries, or arrays with the compound types having the same
	 *      restrictions when $traverse is true or a dictionary whose values
	 *      are null, string, int, and float otherwise
	 */
	public function serialize($data, bool $traverse = false): array {
		$type = $data->type();

		if ($traverse) {
			if (CardType::USER == $type) {
				$user_transformer = new UserTransformer();
				return [
					'id' => $data->id(),
					'card_type_id' => $card_type_id,
					'card_type' => $type->name(),
					'user' => is_null($data->user()) ? null : $user_transformer->serialize($data->user(), $traverse),
					'equipment_type' => null
				];
			} else if (CardType::TRAINING == $type) {
				$equipment_type_transformer = new EquipmentTypeTransformer();
				return [
					'id' => $data->id(),
					'card_type_id' => $card_type_id,
					'card_type' => $type->name(),
					'user' => null,
					'equipment_type' => is_null($data->equipment_type()) ? null : $equipment_type_transformer->serialize($data->equipment_type(), $traverse)
				];
			} else if (CardType::PROXY == $type) {
				return [
					'id' => $data->id(),
					'card_type_id' => $card_type_id,
					'card_type' => $type->name(),
					'user' => null,
					'equipment_type' => null
				];
			} else if (CardType::SHUTDOWN == $type) {
				return [
					'id' => $data->id(),
					'card_type_id' => $card_type_id,
					'card_type' => $type->name(),
					'user' => null,
					'equipment_type' => null
				];
			}
		} else {
			if (CardType::USER == $type) {
				return [
					'id' => $data->id(),
					'card_type_id' => $card_type_id,
					'card_type' => $type->name(),
					'user_id' => $data->user_id(),
					'user' => is_null($data->user()) ? '' : $data->user()->name(),
					'equipment_type_id' => '',
					'equipment_type' => ''
				];
			} else if (CardType::TRAINING == $type) {
				$equipment_type = $data->equipment_type();
				return [
					'id' => $data->id(),
					'card_type_id' => $card_type_id,
					'card_type' => $type->name(),
					'user_id' => '',
					'user' => '',
					'equipment_type_id' => $data->equipment_type_id(),
					'equipment_type' => is_null($equipment_type) ? '' : $equipment_type->name()
				];
			} else if (CardType::PROXY == $type) {
				return [
					'id' => $data->id(),
					'card_type_id' => $card_type_id,
					'card_type' => $type->name(),
					'user_id' => '',
					'user' => '',
					'equipment_type_id' => '',
					'equipment_type' => ''
				];
			} else if (CardType::SHUTDOWN == $type) {
				return [
					'id' => $data->id(),
					'card_type_id' => $card_type_id,
					'card_type' => $type->name(),
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
	 * @return array - a list of strings that can be column headers
	 */
	public function get_column_headers(): array {
		return ['id', 'Card Type ID', 'Card Type', 'User ID', 'User', 'Equipment Type ID', 'Equipment Type'];
	}
}
