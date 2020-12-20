<?php

namespace Portalbox\Entity;

/**
 * Payment represents a payment made by the user to the operator of the
 * portalbox network
 * 
 * @package Portalbox\Entity
 */
class Payment extends AbstractEntity {

	/**
	 * The id of the user who paid
	 *
	 * @var int
	 */
	protected $user_id;

	/**
	 * The amount of this payment
	 *
	 * @var string
	 */
	protected $amount;

	/**
	 * The time of this payment
	 *
	 * @var string
	 */
	protected $time;

	/**
	 * Get the id of the user who paid
	 *
	 * @return int - the id of the user who paid
	 */
	public function user_id() : int {
		return $this->user_id;
	}

	/**
	 * Set the id of the user who paid
	 *
	 * @param int user_id - the id of the user who paid
	 * @return self
	 */
	public function set_user_id(int $user_id) : self {
		$this->user_id = $user_id;
		return $this;
	}

	/**
	 * Get the user who paid
	 *
	 * @return User|null - the user who paid
	 */
	public function user() : ?User {
		return $this->user;
	}

	/**
	 * Set the user who paid
	 *
	 * @param User user - the user who paid
	 * @return self
	 */
	public function set_user(User $user) : self {
		$this->user = $user;
		$this->user_id = $user->id();
		return $this;
	}

	/**
	 * Get the amount of this payment
	 *
	 * @return string - the amount of this payment
	 */
	public function amount() : string {
		return $this->amount;
	}

	/**
	 * Set the amount of this payment
	 *
	 * @param string amount - the amount of this payment
	 * @return self
	 */
	public function set_amount(string $amount) : self {
		$this->amount = $amount;
		return $this;
	}

	/**
	 * Get the time of this payment
	 *
	 * @return string - the time of this payment
	 */
	public function time() : string {
		return $this->time;
	}

	/**
	 * Set the time of this payment
	 *
	 * @param string time - the time of this payment
	 * @return self
	 */
	public function set_time(string $time) : self {
		$this->time = $time;
		return $this;
	}
}
