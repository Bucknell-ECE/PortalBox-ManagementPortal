<?php

namespace Portalbox\Query;

/**
 * LogEventQuery presents a standard interface for LogEvent search queries
 * 
 * @package Portalbox\Query
 */
class LogEventQuery {
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
	 * Get the on or before date
	 *
	 * @return string - the on or before date
	 */
	public function on_or_before() : string {
		return $this->on_or_after;
	}

	/**
	 * Set the on or before date
	 *
	 * @param string on_or_before - the on or before date
	 * @return LogEventQuery - returns this in order to support fluent syntax.
	 */
	public function set_on_or_before(string $on_or_before) : LogEventQuery {
		$this->on_or_before = $on_or_before;
		return $this;
	}

	/**
	 * Get the on or after date
	 *
	 * @return string - the on or after date
	 */
	public function on_or_after() : string {
		return $this->on_or_after;
	}

	/**
	 * Set the on or after date
	 *
	 * @param string on_or_after - the on or after date
	 * @return LogEventQuery - returns this in order to support fluent syntax.
	 */
	public function set_on_or_after(string $on_or_after) : LogEventQuery {
		$this->on_or_after = $on_or_after;
		return $this;
	}

	/**
	 * Get the equipment id
	 *
	 * @return int - the equipment id
	 */
	public function equipment_id() : int {
		return $this->equipment_id;
	}

	/**
	 * Set the equipment id
	 *
	 * @param int equipment_id - the equipment id
	 * @return LogEventQuery - returns this in order to support fluent syntax.
	 */
	public function set_equipment_id(int $equipment_id) : LogEventQuery {
		$this->equipment_id = $equipment_id;
		return $this;
	}

	/**
	 * Get the location id
	 *
	 * @return int - the location id
	 */
	public function location_id() : int {
		return $this->location_id;
	}

	/**
	 * Set the location id
	 *
	 * @param int location_id - the location id
	 * @return LogEventQuery - returns this in order to support fluent syntax.
	 */
	public function set_location_id(int $location_id) : LogEventQuery {
		$this->location_id = $location_id;
		return $this;
	}
}