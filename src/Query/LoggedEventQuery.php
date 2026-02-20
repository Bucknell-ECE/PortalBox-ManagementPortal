<?php

namespace Portalbox\Query;

use Portalbox\Enumeration\LoggedEventType;

/**
 * LoggedEventQuery presents a standard interface for LoggedEvent search queries
 */
class LoggedEventQuery {
	/**
	 * Find log events on or before this date
	 */
	protected ?string $on_or_before = null;

	/**
	 * Find log events on or before this date
	 */
	protected ?string $on_or_after = null;

	/**
	 * Find log events for this equipment
	 */
	protected ?int $equipment_id = null;

	/**
	 * Find log events from equipment in this location
	 */
	protected ?int $location_id = null;

	/**
	 * Find log events of a given type
	 */
	protected ?LoggedEventType $type = null;

	/**
	 * Find log events of a given equipment type
	 */
	protected ?int $equipment_type_id = null;

	/**
	 * Get the on or before date
	 */
	public function on_or_before(): ?string {
		return $this->on_or_before;
	}

	/**
	 * Set the on or before date
	 */
	public function set_on_or_before(string $on_or_before): self {
		$this->on_or_before = $on_or_before;
		return $this;
	}

	/**
	 * Get the on or after date
	 */
	public function on_or_after(): ?string {
		return $this->on_or_after;
	}

	/**
	 * Set the on or after date
	 */
	public function set_on_or_after(string $on_or_after): self {
		$this->on_or_after = $on_or_after;
		return $this;
	}

	/**
	 * Get the equipment id
	 */
	public function equipment_id(): ?int {
		return $this->equipment_id;
	}

	/**
	 * Set the equipment id
	 */
	public function set_equipment_id(int $equipment_id): self {
		$this->equipment_id = $equipment_id;
		return $this;
	}

	/**
	 * Get the location id
	 */
	public function location_id(): ?int {
		return $this->location_id;
	}

	/**
	 * Set the location id
	 */
	public function set_location_id(int $location_id): self {
		$this->location_id = $location_id;
		return $this;
	}

	/**
	 * Get the type
	 */
	public function type(): ?LoggedEventType {
		return $this->type;
	}

	/**
	 * Set the type
	 */
	public function set_type(LoggedEventType $type): self {
		$this->type = $type;
		return $this;
	}

	/**
	 * Get the equipment type id
	 */
	public function equipment_type_id(): ?int {
		return $this->equipment_type_id;
	}

	/**
	 * Set the equipment type id
	 */
	public function set_equipment_type_id(int $equipment_type_id): self {
		$this->equipment_type_id = $equipment_type_id;
		return $this;
	}
}
