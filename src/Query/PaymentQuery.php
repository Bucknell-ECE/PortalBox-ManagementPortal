<?php

namespace Portalbox\Query;

/**
 * PaymentQuery presents a standard interface for Charge search queries
 */
class PaymentQuery {
	/**
	 * Find payments on or before this date
	 */
	protected ?string $on_or_before = null;

	/**
	 * Find payments on or before this date
	 */
	protected ?string $on_or_after = null;

	/**
	 * Find payments for this user
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
