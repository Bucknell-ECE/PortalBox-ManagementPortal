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
	 * @return Payment - returns this in order to support fluent syntax.
	 */
	public function set_user_id(int $user_id) : Payment {
		$this->user_id = $user_id;
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
	 * @return Payment - returns this in order to support fluent syntax.
	 */
	public function set_amount(string $amount) : Payment {
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
	 * @return Payment - returns this in order to support fluent syntax.
	 */
	public function set_time(string $time) : Payment {
		$this->time = $time;
		return $this;
	}
}
