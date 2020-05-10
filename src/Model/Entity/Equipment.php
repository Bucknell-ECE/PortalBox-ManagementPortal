<?php

namespace Portalbox\Model\Entity;

use Portalbox\Config;
use Portalbox\Entity\Equipment as AbstractEquipment;
use Portalbox\Entity\EquipmentType;
use Portalbox\Entity\Location;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Model\LocationModel;

class Equipment extends AbstractEquipment {
	/**
	 * The configuration to use
	 * 
	 * @var Config
	 */
	private $configuration;

	/**
	 * @param Config configuration - the configuration to use
	 */
	public function __construct(Config $configuration) {
		$this->set_configuration($configuration);
	}

	/**
	 * Get the configuration to use
	 *
	 * @return Config - the configuration to use
	 */
	public function configuration() : Config {
		return $this->configuration;
	}

	/**
	 * Set the configuration to use
	 *
	 * @param Config configuration - the configuration to use
	 */
	public function set_configuration(Config $configuration) {
		$this->configuration = $configuration;
	}

	/**
	 * Get the equipment's type
	 *
	 * @return EquipmentType|null - the equipment's type
	 */
	public function type() : ?EquipmentType {
		if(NULL === $this->type) {
			$this->type = (new EquipmentTypeModel($this->configuration()))->read($this->type_id());
		}

		return $this->type;
	}

	/**
	 * Get the equipment's location
	 *
	 * @return Location|null - the equipment's location
	 */
	public function location() : ?Location {
		if(NULL === $this->location) {
			$this->location = (new LocationModel($this->configuration()))->read($this->location_id());
		}

		return $this->location;
	}
}
