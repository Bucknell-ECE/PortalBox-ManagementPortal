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
	 * The the user who was charged
	 *
	 * @var User
	 */
	protected $user;

	/**
	 * The id of the equipment the user used
	 *
	 * @var int
	 */
	protected $equipment_id;

	/**
	 * The the equipment the user used
	 *
	 * @var Equipment
	 */
	protected $equipment;

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
	 * @return self
	 */
	public function set_user_id(int $user_id) : self {
		$this->user_id = $user_id;
		return $this;
	}

	/**
	 * Get the charged user's name
	 *
	 * @return string - the charged user's name
	 */
	public function user_name() : string {
		return $this->user->name();
	}

	/**
	 * Get the charged user
	 *
	 * @return User|null - the charged user
	 */
	public function user() : ?User {
		return $this->user;
	}

	/**
	 * Set the charged user
	 *
	 * @param User user - the charged user
	 * @return self
	 */
	public function set_user(User $user) : self {
		$this->user = $user;
		$this->user_id = $user->id();
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
	 * @return self
	 */
	public function set_equipment_id(int $equipment_id) : self {
		$this->equipment_id = $equipment_id;
		return $this;
	}

	/**
	 * Get the name of the equipment the user used to incur the Charge
	 *
	 * @return string - the name of the equipment the user used to incur the Charge
	 */
	public function equipment_name() : string {
		return $this->equipment->name();
	}

	/**
	 * Get the equipment the user used to incur the Charge
	 *
	 * @return Equipment|null - the equipment the user used to incur the Charge
	 */
	public function equipment() : ?Equipment {
		return $this->equipment;
	}

	/**
	 * Set the equipment the user used to incur the Charge
	 *
	 * @param Equipment equipment - the equipment the user used to incur the Charge
	 * @return self
	 */
	public function set_equipment(Equipment $equipment) : self {
		$this->equipment = $equipment;
		$this->equipment_id = $equipment->id();
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
	 * @return self
	 */
	public function set_time(string $time) : self {
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
	 * @return self
	 */
	public function set_amount(string $amount) : self {
		$this->amount = $amount;
		return $this;
	}

	/**
	 * Get the policy for this charge
	 *
	 * @return string - name for the charge policy
	 */
	public function charge_policy() : string {
		return ChargePolicy::name_for_policy($this->charge_policy_id);
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
	 * @return self
	 */
	public function set_charge_policy_id(int $charge_policy_id) : self {
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
	 * @return self
	 */
	public function set_charge_rate(string $charge_rate) : self {
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
	 * @return self
	 */
	public function set_charged_time(int $charged_time) : self {
		$this->charged_time = $charged_time;
		return $this;
	}
}
