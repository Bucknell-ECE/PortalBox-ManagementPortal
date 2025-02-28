<?php

namespace Portalbox\Model\Entity;

use Portalbox\Config;

use Portalbox\Entity\CardType;
use Portalbox\Entity\LoggedEvent as AbstractLoggedEvent;

class LoggedEvent extends AbstractLoggedEvent {
	/**
	 * The configuration to use
	 */
	private Config $configuration;

	/**
	 * The id of this event's card's type
	 */
	private ?int $card_type_id = NULL;

	/**
	 * The name of this event's equipment's name
	 */
	private ?string $equipment_name = NULL;

	/**
	 * The name of this event's equipment's location's name
	 */
	private ?string $location_name = NULL;

	/**
	 * The name of this event's user's name
	 */
	private ?string $user_name = NULL;

	/**
	 * @param Config configuration - the configuration to use
	 */
	public function __construct(Config $configuration) {
		$this->set_configuration($configuration);
	}

	/**
	 * Get the configuration to use
	 */
	public function configuration(): Config {
		return $this->configuration;
	}

	/**
	 * Set the configuration to use
	 */
	public function set_configuration(Config $configuration): void {
		$this->configuration = $configuration;
	}

	/**
	 * Get this event's card's type id
	 */
	public function card_type_id(): ?int {
		return $this->card_type_id;
	}

	/**
	 * Set this event's card's type id
	 */
	public function set_card_type_id(?int $card_type_id): LoggedEvent {
		$this->card_type_id = $card_type_id;
		return $this;
	}

	/**
	 * Get this event's equipment's name
	 */
	public function equipment_name(): ?string {
		return $this->equipment_name;
	}

	/**
	 * Set this event's equipment's name
	 */
	public function set_equipment_name(?string $equipment_name): LoggedEvent {
		$this->equipment_name = $equipment_name;
		return $this;
	}

	/**
	 * Get this event's equipment's location's name
	 */
	public function location_name(): ?string {
		return $this->location_name;
	}

	/**
	 * Set this event's equipment's location's name
	 */
	public function set_location_name(string $location_name): LoggedEvent {
		$this->location_name = $location_name;
		return $this;
	}

	/**
	 * Get this event's user's name
	 */
	public function user_name(): string {
		if(CardType::TRAINING == $this->card_type_id) {
			return 'Trainer';
		}

		return $this->user_name ?? '';
	}

	/**
	 * Set this event's user's name
	 */
	public function set_user_name(?string $user_name): LoggedEvent {
		$this->user_name = $user_name;
		return $this;
	}
}
