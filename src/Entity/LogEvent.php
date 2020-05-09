<?php

namespace Portalbox\Entity;

/**
 * LogEvent represents one Event in the event log
 * 
 * @package Portalbox\Entity
 */
class LogEvent extends AbstractEntity {

	/**
	 * The time this event occured
	 *
	 * @var string
	 */
	protected $time;

	/**
	 * The event type
	 *
	 * @var int
	 */
	protected $type;
	
	/**
	 * The id of the card which triggered this event
	 *
	 * @var int
	 */
	protected $card_id;

	/**
	 * The card which triggered this event
	 * 
	 * @var Card|null
	 */
	protected $card;

	/**
	 * The id of the user whose card which triggered this event
	 *
	 * @var int
	 */
	protected $user_id;

	/**
	 * The user whose card which triggered this event
	 * 
	 * @var User|null
	 */
	protected $user;

	/**
	 * The id of the equipment which reported this event
	 *
	 * @var int
	 */
	protected $equipment_id;

	/**
	 * The equipment which reported this event
	 * 
	 * @var Equipment|null
	 */
	protected $equipment;

	/**
	 * Get the time this event occured
	 *
	 * @return string - the time this event occured
	 */
	public function time() : string {
		return $this->time;
	}

	/**
	 * Set the time this event occured
	 *
	 * @param string time - the time this event occured
	 * @return LoggedEvent - returns this in order to support fluent syntax.
	 */
	public function set_time(string $time) : LoggedEvent {
		$this->time = $time;
		return $this;
	}

	/**
	 * Get the type of this event
	 *
	 * @return int - the type of this event
	 */
	public function type() : int {
		return $this->type;
	}

	/**
	 * Set the type of this event
	 *
	 * @param int type - the type of this event
	 * @return LoggedEvent - returns this in order to support fluent syntax.
	 */
	public function set_type(int $type) : LoggedEvent {
		$this->type = $type;
		return $this;
	}

	/**
	 * Get the id of the equipment which reported this event
	 *
	 * @return int - the id of the equipment which reported this event
	 */
	public function equipment_id() : int {
		return $this->equipment_id;
	}

	/**
	 * Set the id of the equipment which reported this event
	 *
	 * @param int equipment_id - the id of the equipment which reported this event
	 * @return LoggedEvent - returns this in order to support fluent syntax.
	 */
	public function set_equipment_id(int $equipment_id) : LoggedEvent {
		$this->equipment_id = $equipment_id;
		$this->equipment = NULL;
		return $this;
	}

	/**
	 * Get the name of the equipment which reported this event
	 * 
	 * @return string - the name of the user's equipment
	 */
	public function equipment_name() : string {
		if(NULL === $equipment) {
			return '';
		} else {
			return $equipment->name();
		}
	}

	/**
	 * Get the equipment which reported this event
	 *
	 * @return Equipment|null - the equipment which reported this event
	 */
	public function equipment() : ?Equipment {
		return $this->equipment;
	}

	/**
	 * Set the equipment which reported this event
	 *
	 * @param Equipment|null equipment - the equipment which reported this event
	 * @return LoggedEvent - returns this in order to support fluent syntax.
	 */
	public function set_equipment(?Equipment $equipment) : LoggedEvent {
		$this->equipment = $equipment;
		if(NULL === $equipment) {
			$this->equipment_id = -1;
		} else {
			$this->equipment_id = $equipment->id();
		}

		return $this;
	}

	/**
	 * Get the id of the card which triggered this event
	 *
	 * @return int - the id of the card which triggered this event
	 */
	public function card_id() : int {
		return $this->card_id;
	}

	/**
	 * Set the id of the card which triggered this event
	 *
	 * @param int card_id - the id of the card which triggered this event
	 * @return LoggedEvent - returns this in order to support fluent syntax.
	 */
	public function set_card_id(int $card_id) : LoggedEvent {
		$this->card_id = $card_id;
		$this->card = NULL;
		return $this;
	}

	/**
	 * Get the card which triggered this event
	 *
	 * @return Card|null - the card which triggered this event
	 */
	public function card() : ?Card {
		return $this->card;
	}

	/**
	 * Set the card which triggered this event
	 *
	 * @param Card|null card - the card which triggered this event
	 * @return LoggedEvent - returns this in order to support fluent syntax.
	 */
	public function set_card(?Card $card) : LoggedEvent {
		$this->card = $card;
		if(NULL === $card) {
			$this->card_id = -1;
		} else {
			$this->card_id = $card->id();
		}

		return $this;
	}

	/**
	 * Get the id of the user whose card which triggered this event
	 *
	 * @return int - the id of the user whose card which triggered this event
	 */
	public function user_id() : int {
		return $this->user_id;
	}

	/**
	 * Set the id of the user whose card which triggered this event
	 *
	 * @param int user_id - the id of the user whose card which triggered this event
	 * @return LoggedEvent - returns this in order to support fluent syntax.
	 */
	public function set_user_id(int $user_id) : LoggedEvent {
		$this->user_id = $user_id;
		$this->user = NULL;
		return $this;
	}

	/**
	 * Get the user whose card which triggered this event
	 *
	 * @return User|null - the user whose card which triggered this event
	 */
	public function user() : ?User {
		return $this->user;
	}

	/**
	 * Set the user whose card which triggered this event
	 *
	 * @param User|null user - the user whose card which triggered this event
	 * @return LoggedEvent - returns this in order to support fluent syntax.
	 */
	public function set_user(?User $user) : LoggedEvent {
		$this->user = $user;
		if(NULL === $user) {
			$this->user_id = -1;
		} else {
			$this->user_id = $user->id();
		}

		return $this;
	}
}
