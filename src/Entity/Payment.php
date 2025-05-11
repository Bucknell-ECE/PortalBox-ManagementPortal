<?php

namespace Portalbox\Entity;

/**
 * Payment represents a payment made by the user to the operator of the
 * portalbox network
 */
class Payment {
	use \Portalbox\Trait\HasIdProperty;

	/** The id of the user who paid */
	protected int $user_id = -1;

	/** The amount of this payment */
	protected string $amount = '';

	/** The time of this payment */
	protected string $time = '';

	/** The user who made this payment */
	private ?User $user = null;

	/** Get the id of the user who paid */
	public function user_id() : int {
		return $this->user_id;
	}

	/** Set the id of the user who paid */
	public function set_user_id(int $user_id) : self {
		$this->user_id = $user_id;
		return $this;
	}

	/** Get the user who paid */
	public function user() : ?User {
		return $this->user;
	}

	/** Set the user who paid */
	public function set_user(User $user) : self {
		$this->user = $user;
		$this->user_id = $user->id();
		return $this;
	}

	/** Get the amount of this payment */
	public function amount() : string {
		return $this->amount;
	}

	/** Set the amount of this payment */
	public function set_amount(string $amount) : self {
		$this->amount = $amount;
		return $this;
	}

	/** Get the time of this payment */
	public function time() : string {
		return $this->time;
	}

	/** Set the time of this payment */
	public function set_time(string $time) : self {
		$this->time = $time;
		return $this;
	}
}
