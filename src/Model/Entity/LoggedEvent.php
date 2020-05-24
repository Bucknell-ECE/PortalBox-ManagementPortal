<?php

namespace Portalbox\Model\Entity;

use Portalbox\Config;

use Portalbox\Entity\CardType;
use Portalbox\Entity\LoggedEvent as AbstractLoggedEvent;

class LoggedEvent extends AbstractLoggedEvent {
	/**
	 * The configuration to use
	 * 
	 * @var Config
	 */
	private $configuration;

	/**
	 * The id of this event's card's type
	 *
	 * @var string
	 */
	private $card_type_id;

	/**
	 * The name of this event's equipment's name
	 *
	 * @var string
	 */
	private $equipment_name;

	/**
	 * The name of this event's equipment's location's name
	 *
	 * @var string
	 */
	private $location_name;

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
	 * Get this event's card's type id
	 * 
	 * @return int|null - the event's card's type id
	 */
	public function card_type_id() : ?int {
		return $this->card_type_id;
	}

	/**
	 * Set this event's card's type id
	 * 
	 * @param int|null card_type_id - the event's card's type id
	 */
	public function set_card_type_id(string $card_type_id) : LoggedEvent {
		$this->card_type_id = $card_type_id;
		return $this;
	}

	/**
	 * Get this event's equipment's name
	 * 
	 * @return string - the name of event's equipment
	 */
	public function equipment_name() : ?string {
		return $this->equipment_name;
	}

	/**
	 * Set this event's equipment's name
	 * 
	 * @param string name - the name of the event's equipment
	 */
	public function set_equipment_name(string $equipment_name) : LoggedEvent {
		$this->equipment_name = $equipment_name;
		return $this;
	}

	/**
	 * Get this event's equipment's location's name
	 * 
	 * @return string - the name of the user's role
	 */
	public function location_name() : ?string {
		return $this->location_name;
	}

	/**
	 * Set this event's equipment's location's name
	 * 
	 * @param string name - the name of this event's equipment's location
	 */
	public function set_location_name(string $location_name) : LoggedEvent {
		$this->location_name = $location_name;
		return $this;
	}

	/**
	 * Get this event's user's name
	 * 
	 * @return string - the name of the event's user
	 */
	public function user_name() : ?string {
		if(CardType::TRAINING == $this->card_type_id) {
			return 'Trainer';
		}

		return $this->user_name;
	}

	/**
	 * Set this event's user's name
	 * 
	 * @param string name - the name of this event's user
	 */
	public function set_user_name(?string $user_name) : LoggedEvent {
		$this->user_name = $user_name;
		return $this;
	}
}
