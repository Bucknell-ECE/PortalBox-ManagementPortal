<?php

namespace Portalbox\Entity;

/**
 * LoggedEvent represents one Event in the event log
 */
class LoggedEvent {
	use \Portalbox\Trait\HasIdProperty;

	/** The time this event occurred */
	protected string $time = '';

	/** The event type */
	protected int $type_id = -1;

	/** The id of the card which triggered this event */
	protected int $card_id = -1;

	/** The card which triggered this event */
	protected ?Card $card = NULL;

	/** The id of the user whose card which triggered this event */
	protected int $user_id = -1;

	/** The user whose card which triggered this event */
	protected ?User $user = NULL;

	/** The id of the equipment which reported this event */
	protected int $equipment_id = -1;

	/** The name of the equipment which reported this event */
	protected ?Equipment $equipment = NULL;

	/** The id of the equipment type for this event */
	private int $equipment_type_id = -1;

	/** The name of the equipment type for this event */
	private string $equipment_type = '';


	/** Get the time this event occurred */
	public function time() : string {
		return $this->time;
	}

	/** Set the time this event occurred */
	public function set_time(string $time) : self {
		$this->time = $time;
		return $this;
	}

	/** Get the type of this event */
	public function type_id() : int {
		return $this->type_id;
	}

	/** Set the type_id of this event */
	public function set_type_id(int $type_id) : self {
		$this->type_id = $type_id;
		return $this;
	}

	/** Get the event type for this event */
	public function type() : string {
		return LoggedEventType::name_for_type($this->type_id);
	}

	/** Get the id of the equipment which reported this event */
	public function equipment_id() : int {
		return $this->equipment_id;
	}

	/** Set the id of the equipment which reported this event */
	public function set_equipment_id(int $equipment_id) : self {
		$this->equipment_id = $equipment_id;
		$this->equipment = NULL;
		return $this;
	}

	// a convenience method... see Model\Entity\LoggedEventModel ;)
	/** Get the name of the equipment which reported this event */
	public function equipment_name() : ?string {
		if(NULL === $this->equipment) {
			return '';
		}

		return $this->equipment->name();
	}

	/** Get the equipment which reported this event */
	public function equipment() : ?Equipment {
		return $this->equipment;
	}

	/** Set the equipment which reported this event */
	public function set_equipment(?Equipment $equipment) : self {
		$this->equipment = $equipment;
		if(NULL === $equipment) {
			$this->equipment_id = -1;
		} else {
			$this->equipment_id = $equipment->id();
		}

		return $this;
	}

	/** Get the id of the card which triggered this event */
	public function card_id() : ?int {
		return $this->card_id;
	}

	/** Set the id of the card which triggered this event */
	public function set_card_id(int $card_id) : self {
		$this->card_id = $card_id;
		$this->card = NULL;
		return $this;
	}

	/** Get the card which triggered this event */
	public function card() : ?Card {
		return $this->card;
	}

	/** Set the card which triggered this event */
	public function set_card(?Card $card) : self {
		$this->card = $card;
		if(NULL === $card) {
			$this->card_id = -1;
		} else {
			$this->card_id = $card->id();
		}

		return $this;
	}

	/** Get the id of the user whose card which triggered this event */
	public function user_id() : int {
		return $this->user_id;
	}

	/** Set the id of the user whose card which triggered this event */
	public function set_user_id(int $user_id) : self {
		$this->user_id = $user_id;
		$this->user = NULL;
		return $this;
	}

	// a convenience method... see Model\Entity\LoggedEventModel ;)
	/** Get the name of the user whose action triggered this event */
	public function user_name() : string {
		if(NULL === $this->user) {
			return '';
		}

		return $this->user->name();
	}

	/** Get the user whose card which triggered this event */
	public function user() : ?User {
		return $this->user;
	}

	/** Set the user whose card which triggered this event */
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
	 * Get the name of the location where the equipment which triggered this
	 * event is located
	 */
	public function location_name() : ?string {
		if(NULL === $this->equipment) {
			return '';
		}

		return $this->equipment->location()->name();
	}

	public function equipment_type_id() : ?int {
		return $this->equipment_type_id;
	}

	public function set_equipment_type_id(int $equipment_type_id) : self {
		$this->equipment_type_id = $equipment_type_id;
		return $this;
	}

	public function equipment_type() : ?string {
		return $this->equipment_type;
	}

	public function set_equipment_type(string $equipment_type) : self {
		$this->equipment_type = $equipment_type;
		return $this;
	}
}
