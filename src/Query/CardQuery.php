<?php

namespace Portalbox\Query;

/**
 * CardQuery presents a standard interface for Card search queries
 */
class CardQuery {
	/**
	 * Find cards for this equipment type
	 */
	protected ?int $equipment_type_id = null;

	/**
	 * Find cards for this user
	 */
	protected ?int $user_id = null;

	/**
	 * Find cards for this id
	 */
	protected ?int $id = null;

	/**
	 * Get the equipment type id
	 */
	public function equipment_type_id(): ?int {
		return $this->equipment_type_id;
	}

	/**
	 * Set the equipment type id
	 */
	public function set_equipment_type_id(int $type_id): self {
		$this->equipment_type_id = $type_id;
		return $this;
	}

	/**
	 * Get the user id
	 */
	public function user_id(): ?int {
		return $this->user_id;
	}

	/**
	 * Set the user id
	 */
	public function set_user_id(int $user_id): self {
		$this->user_id = $user_id;
		return $this;
	}

	/**
	 * Get the card id
	 */
	public function id(): ?int {
		return $this->id;
	}

	/**
	 * Set the card id
	 */
	public function set_id(int $id): self {
		$this->id = $id;
		return $this;
	}
}
