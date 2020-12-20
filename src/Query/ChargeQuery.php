<?php

namespace Portalbox\Query;

/**
 * ChargeQuery presents a standard interface for Charge search queries
 * 
 * @package Portalbox\Query
 */
class ChargeQuery {
	/**
	 * Find charges on or before this date
	 *
	 * @var string
	 */
	protected $on_or_before;

	/**
	 * Find charges on or before this date
	 *
	 * @var string
	 */
	protected $on_or_after;

	/**
	 * Find charges for this equipment
	 *
	 * @var int
	 */
	protected $equipment_id;

	/**
	 * Find charges for this user
	 *
	 * @var int
	 */
	protected $user_id;


	/**
	 * Get the on or before date
	 *
	 * @return string - the on or before date
	 */
	public function on_or_before() : ?string {
		return $this->on_or_before;
	}

	/**
	 * Set the on or before date
	 *
	 * @param string on_or_before - the on or before date
	 * @return self
	 */
	public function set_on_or_before(string $on_or_before) : self {
		$this->on_or_before = $on_or_before;
		return $this;
	}

	/**
	 * Get the on or after date
	 *
	 * @return string - the on or after date
	 */
	public function on_or_after() : ?string {
		return $this->on_or_after;
	}

	/**
	 * Set the on or after date
	 *
	 * @param string on_or_after - the on or after date
	 * @return self
	 */
	public function set_on_or_after(string $on_or_after) : self {
		$this->on_or_after = $on_or_after;
		return $this;
	}

	/**
	 * Get the equipment id
	 *
	 * @return int - the equipment id
	 */
	public function equipment_id() : ?int {
		return $this->equipment_id;
	}

	/**
	 * Set the equipment id
	 *
	 * @param int equipment_id - the equipment id
	 * @return self
	 */
	public function set_equipment_id(int $equipment_id) : self {
		$this->equipment_id = $equipment_id;
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
}