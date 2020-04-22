<?php

namespace Portalbox\Entity;

/**
 * Charge represents a charge to a user for use of equipment in the
 * portalbox network
 * 
 * @package Portalbox\Entity
 */
class Charge extends AbstractEntity {

	/**
	 * The id of the user who was charged
	 *
	 * @var int
	 */
	protected $user_id;

	/**
	 * The id of the equipment the user used
	 *
	 * @var int
	 */
	protected $equipment_id;

	/**
	 * The time of this charge
	 *
	 * @var string
	 */
	protected $time;

	/**
	 * The amount of this charge
	 *
	 * @var string
	 */
	protected $amount;

	/**
	 * The id of the charge policy in effect during the creation of the Charge
	 *
	 * @var int
	 */
	protected $charge_policy_id;

	/**
	 * The charge rate in effect during the creation of the Charge
	 *
	 * @var string
	 */
	protected $charge_rate;

	/**
	 * The duration in seconds of the activation session that resulted in the
	 * creation of this Charge
	 *
	 * @var int
	 */
	protected $charged_time;

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
	 * @return Charge - returns this in order to support fluent syntax.
	 */
	public function set_user_id(int $user_id) : Charge {
		$this->user_id = $user_id;
		return $this;
	}

	/**
	 * Get the id of the equipment the user used to incur the Charge
	 *
	 * @return int - the id of the equipment the user used to incur the Charge
	 */
	public function equipment_id() : int {
		return $this->equipment_id;
	}

	/**
	 * Set the id of the equipment the user used to incur the Charge
	 *
	 * @param int equipment_id - the id of the equipment the user used to incur the Charge
	 * @return Charge - returns this in order to support fluent syntax.
	 */
	public function set_equipment_id(int $equipment_id) : Charge {
		$this->equipment_id = $equipment_id;
		return $this;
	}

	/**
	 * Get the time of this charge
	 *
	 * @return string - the time of this charge
	 */
	public function time() : string {
		return $this->time;
	}

	/**
	 * Set the time of this charge
	 *
	 * @param string time - the time of this charge
	 * @return Charge - returns this in order to support fluent syntax.
	 */
	public function set_time(string $time) : Charge {
		$this->time = $time;
		return $this;
	}

	/**
	 * Get the amount of this charge
	 *
	 * @return string - the amount of this charge
	 */
	public function amount() : string {
		return $this->amount;
	}

	/**
	 * Set the amount of this charge
	 *
	 * @param string amount - the amount of this charge
	 * @return Charge - returns this in order to support fluent syntax.
	 */
	public function set_amount(string $amount) : Charge {
		$this->amount = $amount;
		return $this;
	}

	/**
	 * Get the id of the charge policy in effect during the creation of the Charge
	 *
	 * @return int - the id of the charge policy in effect during the creation of the Charge
	 */
	public function charge_policy_id() : int {
		return $this->charge_policy_id;
	}

	/**
	 * Set the id of the charge policy in effect during the creation of the Charge
	 *
	 * @param int charge_policy_id - the id of the charge policy in effect during the creation of the Charge
	 * @return Charge - returns this in order to support fluent syntax.
	 */
	public function set_charge_policy_id(int $charge_policy_id) : Charge {
		$this->charge_policy_id = $charge_policy_id;
		return $this;
	}

	/**
	 * Get the charge rate in effect during the creation of the Charge
	 *
	 * @return string - the charge rate in effect during the creation of the Charge
	 */
	public function charge_rate() : string {
		return $this->charge_rate;
	}

	/**
	 * Set the charge rate in effect during the creation of the Charge
	 *
	 * @param string charge_rate - the charge rate in effect during the creation of the Charge
	 * @return Charge - returns this in order to support fluent syntax.
	 */
	public function set_charge_rate(int $charge_rate) : Charge {
		$this->charge_rate = $charge_rate;
		return $this;
	}

	/**
	 * Get duration in seconds of the activation session that resulted in the
	 * creation of this Charge
	 *
	 * @return int - the duration in seconds of the activation session that
	 * resulted in the creation of this Charge
	 */
	public function charged_time() : int {
		return $this->charged_time;
	}

	/**
	 * Set duration in seconds of the activation session that resulted in the
	 * creation of this Charge
	 *
	 * @param int charged_time - the duration in seconds of the activation session that resulted in the
	 * creation of this Charge
	 * @return Charge - returns this in order to support fluent syntax.
	 */
	public function set_charged_time(int $charged_time) : Charge {
		$this->charged_time = $charged_time;
		return $this;
	}
}
