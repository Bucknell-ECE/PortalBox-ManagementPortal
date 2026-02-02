<?php

namespace Portalbox\Model\Type;

use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Type\EquipmentType;

/**
 * A database aware user card (can read the equipment type from the database)
 */
class TrainingCard extends \Portalbox\Type\TrainingCard {
	/** The model used to read equipment types */
	private EquipmentTypeModel $model;

	public function __construct(EquipmentTypeModel $model) {
		$this->model = $model;
	}

	/** Get the type of equipment this card can activate for training */
	public function equipment_type(): ?EquipmentType {
		if ($this->equipment_type === null) {
			$this->equipment_type = $this->model->read($this->equipment_type_id());
		}

		return $this->equipment_type;
	}
}
