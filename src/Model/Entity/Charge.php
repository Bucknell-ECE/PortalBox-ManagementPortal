<?php

namespace Portalbox\Model\Entity;

use Portalbox\Config;

use Portalbox\Entity\Charge as AbstractCharge;
use Portalbox\Entity\Equipment;
use Portalbox\Entity\User;

use Portalbox\Model\EquipmentModel;
use Portalbox\Model\UserModel;

class Charge extends AbstractCharge {
	/**
	 * The configuration to use
	 * 
	 * @var Config
	 */
	private $configuration;

	/**
	 * The name of this event's equipment's name
	 *
	 * @var string
	 */
	private $equipment_name;

	/**
	 * The name of this event's user's name
	 *
	 * @var string
	 */
	private $user_name;

	
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
	 * Get the name of the equipment the user used to incur the Charge
	 *
	 * @return string - the name of the equipment the user used to incur the Charge
	 */
	public function equipment_name() : ?string {
		return $this->equipment_name;
	}

	/**
	 * Set the name of the equipment the user used to incur the Charge
	 * 
	 * @param string name - the name of the equipment the user used to incur the Charge
	 */
	public function set_equipment_name(string $equipment_name) : Charge {
		$this->equipment_name = $equipment_name;
		return $this;
	}

	/**
	 * Get the equipment the user used to incur the Charge
	 *
	 * @return Equipment|null - the equipment the user used to incur the Charge
	 */
	public function equipment() : ?Equipment {
		if(NULL === $this->equipment) {
			$this->equipment = (new EquipmentModel($this->configuration()))->read($this->equipment_id());
		}

		return $this->equipment;
	}

	/**
	 * Get the charged user's name
	 *
	 * @return string - the charged user's name
	 */
	public function user_name() : ?string {
		return $this->user_name;
	}

	/**
	 * Set the charged user's name
	 * 
	 * @param string name - the charged user's name
	 */
	public function set_user_name(?string $user_name) : Charge {
		$this->user_name = $user_name;
		return $this;
	}

	/**
	 * Get the charged user
	 *
	 * @return User|null - the charged user
	 */
	public function user() : ?User {
		if(NULL === $this->user) {
			$this->user = (new UserModel($this->configuration()))->read($this->user_id());
		}

		return $this->user;
	}
}
