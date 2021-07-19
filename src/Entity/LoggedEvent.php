<?php

namespace Portalbox\Entity;

/**
 * LoggedEvent represents one Event in the event log
 * 
 * @package Portalbox\Entity
 */
class LoggedEvent extends AbstractEntity {

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
	protected $type_id;
	
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
	 * The name of the equipment which reported this event
	 * 
	 * @var Equipment|null
	 */
	protected $equipment;

	
	/**
	 * The id of the equipment type for this event
	 * 
	 * @var int
	 */
	private $equipment_type_id;

	/**
	 * The name of the equipment type for this event
	 * 
	 * @var string
	 */
	private $equipment_type;


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
	 * @return self
	 */
	public function set_time(string $time) : self {
		$this->time = $time;
		return $this;
	}

	/**
	 * Get the type of this event
	 *
	 * @return int - the type of this event
	 */
	public function type_id() : int {
		return $this->type_id;
	}

	/**
	 * Set the type_id of this event
	 *
	 * @param int type_id - the type_id of this event
	 * @return self
	 */
	public function set_type_id(int $type_id) : self {
		$this->type_id = $type_id;
		return $this;
	}

	/**
	 * Get the event type for this event
	 *
	 * @return string - name for the event type
	 */
	public function type() : string {
		return LoggedEventType::name_for_type($this->type_id);
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
	 * @return self
	 */
	public function set_equipment_id(int $equipment_id) : self {
		$this->equipment_id = $equipment_id;
		$this->equipment = NULL;
		return $this;
	}

	// a convenience method... see Model\Entity\LoggedEventModel ;)
	/**
	 * Get the name of the equipment which reported this event
	 * 
	 * @return string - the name of the equipment
	 */
	public function equipment_name() : ?string {
		if(NULL === $this->equipment) {
			return '';
		} else {
			return $this->equipment->name();
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
	 * @return self
	 */
	public function set_equipment(?Equipment $equipment) : self {
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
	public function card_id() : ?int {
		return $this->card_id;
	}

	/**
	 * Set the id of the card which triggered this event
	 *
	 * @param int card_id - the id of the card which triggered this event
	 * @return self
	 */
	public function set_card_id(int $card_id) : self {
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
	 * @return self
	 */
	public function set_card(?Card $card) : self {
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
	 * @return self
	 */
	public function set_user_id(int $user_id) : self {
		$this->user_id = $user_id;
		$this->user = NULL;
		return $this;
	}

	// a convenience method... see Model\Entity\LoggedEventModel ;)
	/**
	 * Get the name of the user whose action triggered this event
	 * 
	 * @return string - the name of the user
	 */
	public function user_name() : ?string {
		if(NULL === $this->user) {
			return '';
		} else {
			return $this->user->name();
		}
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
	 * @return self
	 */
	public function set_user(?User $user) : self {
		$this->user = $user;
		if(NULL === $user) {
			$this->user_id = -1;
		} else {
			$this->user_id = $user->id();
		}

		return $this;
	}

	// a convenience method... see Model\Entity\LoggedEventModel ;)
	/**
	 * Get the name of the location where the equipment which triggerd this
	 * event is located
	 * 
	 * @return string - the name of the location
	 */
	public function location_name() : ?string {
		if(NULL === $this->equipment) {
			return '';
		} else {
			return $this->equipment->location()->name();
		}
	}

	public function equipment_type_id() : ?int {
		return $this->equipment_type_id;
	}

	public function set_equipment_type_id(int $equipment_type_id) : LoggedEvent {
		$this->equipment_type_id = $equipment_type_id;
		return $this;
	}

	public function equipment_type() : ?string {
		return $this->equipment_type;
	}

	public function set_equipment_type(string $equipment_type) : LoggedEvent {
		$this->equipment_type = $equipment_type;
		return $this;
	}
}
