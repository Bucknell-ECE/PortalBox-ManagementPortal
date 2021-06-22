<?php

namespace Portalbox\Entity;

/**
 * Cards come in a number of types and when presented to a portalbox, the
 * portalbox shutsdown when presented with cards of this type.
 * 
 * @package Portalbox\Entity
 */
class TrainingCard extends AbstractEntity implements Card {

	/**
	 * The id of the type of equipment this card can activate for training
	 */
	private $equipment_type_id;

	/**
	 * Get the type of the card
	 *
	 * @return int - type one of the predefined constants exposed by CardType
	 */
	public function type_id() : int {
		return CardType::TRAINING;
	}

	/**
	 * Get the id of the type of equipment this card can activate for training
	 *
	 * @return int - the id of the type of equipment this card can activate for
	 *               training
	 */
	public function equipment_type_id() : int {
		return $this->equipment_type_id;
	}

	/**
	 * Set the id of the type of equipment this card can activate for training
	 *
	 * @param int equipment_type_id - the id of the type of equipment this card
	 *               can activate for training
	 * @return self
	 */
	public function set_equipment_type_id(int $equipment_type_id) : self {
		$this->equipment_type_id = $equipment_type_id;
		$this->equipment_type = NULL;
		return $this;
	}

	/**
	 * Get the type of equipment this card can activate for training
	 *
	 * @return EquipmentType|null - the type of equipment this card can
	 *               activate for training
	 */
	public function equipment_type() : ?EquipmentType {
		return $this->equipment_type;
	}

	public function set_equipment_type(?EquipmentType $equipment_type) : self{
		$this->equipment_type = $equipment_type;
		return $this;
	}

	/**
	 * Set the type of equipment this card can activate for training
	 *
	 * @param EquipmentType|null equipment_type - the type of equipment this
	 *               card can activate for training
	 * @return self
	 */
	public function set_type(?EquipmentType $equipment_type) : self {
		$this->equipment_type = $equipment_type;
		if(NULL === $equipment_type) {
			$this->equipment_type_id = -1;
		} else {
			$this->equipment_type_id = $equipment_type->id();
		}

		return $this;
	}
}
