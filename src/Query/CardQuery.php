<?php

namespace Portalbox\Query;

/**
 * CardQuery presents a standard interface for Card search queries
 * 
 * @package Portalbox\Query
 */
class CardQuery {
	/**
	 * Find cards for this equipment type
	 *
	 * @var int
	 */
	protected $equipment_type_id;

	/**
	 * Find cards for this user
	 *
	 * @var int
	 */
	protected $user_id;

	/**
	 * Find cards for this id
	 * 
	 * @var int
	 */
	protected $id;

	/**
	 * Get the equipment type id
	 *
	 * @return int - the equipment type id
	 */
	public function equipment_type_id() : ?int {
		return $this->equipment_type_id;
	}

	/**
	 * Set the equipment type id
	 *
	 * @param int equipment_type_id - the equipment id
	 * @return self
	 */
	public function set_equipment_type_id(int $type_id) : self {
		$this->equipment_type_id = $type_id;
		return $this;
	}

	/**
	 * Get the user id
	 *
	 * @return int - the user id
	 */
	public function user_id() : ?int {
		return $this->user_id;
	}

	/**
	 * Set the user id
	 *
	 * @param int user_id - the user id
	 * @return self
	 */
	public function set_user_id(int $user_id) : self {
		$this->user_id = $user_id;
		return $this;
	}

	/**
	 * Get the card id
	 * 
	 * @return int - the card id
	 */
	public function id() : ?int  {
		return $this->id;
	}

	/**
	 * Set the card id
	 * 
	 * @param int id - the card id
	 * @return self
	 */
	public function set_id(int $id): self {
		$this->id = $id;
		return $this;
	}
}