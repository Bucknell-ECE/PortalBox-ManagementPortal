<?php

namespace Portalbox\Query;

/**
 * LoggedEventQuery presents a standard interface for LoggedEvent search queries
 * 
 * @package Portalbox\Query
 */
class LoggedEventQuery {
	/**
	 * Find log events on or before this date
	 *
	 * @var string
	 */
	protected $on_or_before;

	/**
	 * Find log events on or before this date
	 *
	 * @var string
	 */
	protected $on_or_after;

	/**
	 * Find log events for this equipment
	 *
	 * @var int
	 */
	protected $equipment_id;

	/**
	 * Find log events from equipment in this location
	 *
	 * @var int
	 */
	protected $location_id;

	/**
	 * Find log events of a given type
	 *
	 * @var int
	 */
	protected $type_id;

	/**
	 * Find log events of a giben equipment type
	 * 
	 * @var int
	 */
	protected $equipment_type_id;

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
	 * Get the location id
	 *
	 * @return int - the location id
	 */
	public function location_id() : ?int {
		return $this->location_id;
	}

	/**
	 * Set the location id
	 *
	 * @param int location_id - the location id
	 * @return self
	 */
	public function set_location_id(int $location_id) : self {
		$this->location_id = $location_id;
		return $this;
	}

	/**
	 * Get the type id
	 *
	 * @return int - the type id
	 */
	public function type_id() : ?int {
		return $this->type_id;
	}

	/**
	 * Set the type id
	 *
	 * @param int type_id - the type id
	 * @return self
	 */
	public function set_type_id(int $type_id) : self {
		$this->type_id = $type_id;
		return $this;
	}

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
	 * @param int equipment_type_id - the equipment type id
	 * @return self
	 */
	public function set_equipment_type_id(int $equipment_type_id) : self {
		$this->equipment_type_id = $equipment_type_id;
		return $this;
	}
}