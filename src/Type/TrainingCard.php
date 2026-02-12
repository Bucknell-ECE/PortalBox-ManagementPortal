<?php

namespace Portalbox\Type;

use Portalbox\Enumeration\CardType;

/**
 * A card which can be used to activate a portalbox for training
 */
class TrainingCard extends Card {
	/**
	 * The id of the type of equipment this card can activate for training
	 */
	protected int $equipment_type_id;

	/**
	 * The type of equipment this card can activate for training
	 */
	protected ?EquipmentType $equipment_type = null;

	/**
	 * Get the type of the card
	 */
	public function type(): CardType {
		return CardType::TRAINING;
	}

	/**
	 * Get the id of the type of equipment this card can activate for training
	 *
	 * @return int - the id of the type of equipment this card can activate for
	 *               training
	 */
	public function equipment_type_id(): int {
		return $this->equipment_type_id;
	}

	/**
	 * Set the id of the type of equipment this card can activate for training
	 *
	 * @param int equipment_type_id - the id of the type of equipment this card
	 *               can activate for training
	 * @return self
	 */
	public function set_equipment_type_id(int $equipment_type_id): self {
		$this->equipment_type_id = $equipment_type_id;
		$this->equipment_type = null;
		return $this;
	}

	/**
	 * Get the type of equipment this card can activate for training
	 *
	 * @return EquipmentType|null - the type of equipment this card can
	 *               activate for training
	 */
	public function equipment_type(): ?EquipmentType {
		return $this->equipment_type;
	}

	/**
	 * Set the type of equipment this card can activate for training
	 *
	 * @param EquipmentType|null equipment_type - the type of equipment this
	 *               card can activate for training
	 * @return self
	 */
	public function set_equipment_type(?EquipmentType $equipment_type): self {
		$this->equipment_type = $equipment_type;
		if (null === $equipment_type) {
			$this->equipment_type_id = -1;
		} else {
			$this->equipment_type_id = $equipment_type->id();
		}

		return $this;
	}
}
