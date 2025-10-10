<?php

namespace Portalbox\Query;

/**
 * ChargeQuery presents a standard interface for Charge search queries
 */
class ChargeQuery {
	/**
	 * Find charges on or before this date
	 */
	protected ?string $on_or_before = null;

	/**
	 * Find charges on or before this date
	 */
	protected ?string $on_or_after = null;

	/**
	 * Find charges for this equipment
	 */
	protected ?int $equipment_id = null;

	/**
	 * Find charges for this user
	 */
	protected ?int $user_id = null;


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
}
