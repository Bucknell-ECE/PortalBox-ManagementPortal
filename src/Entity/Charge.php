<?php

namespace Portalbox\Entity;

/**
 * Charge represents a charge to a user for use of equipment in the
 * portalbox network
 */
class Charge {
	use \Portalbox\Trait\HasIdProperty;

	/** The id of the user who was charged */
	protected int $user_id = -1;

	/** The the user who was charged */
	protected ?User $user = NULL;

	/** The id of the equipment the user used */
	protected int $equipment_id = -1;

	/** The the equipment the user used */
	protected ?Equipment $equipment = NULL;

	/** The time of this charge */
	protected string $time = '';

	/** The amount of this charge */
	protected string $amount = '';

	/**
	 * The id of the charge policy in effect during the creation of the Charge
	 */
	protected int $charge_policy_id = ChargePolicy::MANUALLY_ADJUSTED;

	/** The charge rate in effect during the creation of the Charge */
	protected string $charge_rate = '';

	/**
	 * The duration in seconds of the activation session that resulted in the
	 * creation of this Charge
	 */
	protected int $charged_time = 0;

	/** Get the id of the user who paid */
	public function user_id() : int {
		return $this->user_id;
	}

	/** Set the id of the user who paid */
	public function set_user_id(int $user_id) : self {
		$this->user_id = $user_id;
		return $this;
	}

	/** Get the charged user's name */
	public function user_name() : string {
		$user = $this->user();
		if ($user === NULL) {
			return '';
		}

		return $user->name();
	}

	/** Get the charged user */
	public function user() : ?User {
		return $this->user;
	}

	/** Set the charged user */
	public function set_user(User $user) : self {
		$this->user = $user;
		$this->user_id = $user->id();
		return $this;
	}

	/** Get the id of the equipment the user used to incur the Charge */
	public function equipment_id() : int {
		return $this->equipment_id;
	}

	/** Set the id of the equipment the user used to incur the Charge */
	public function set_equipment_id(int $equipment_id) : self {
		$this->equipment_id = $equipment_id;
		return $this;
	}

	/** Get the name of the equipment the user used to incur the Charge */
	public function equipment_name() : string {
		$equipment = $this->equipment();
		if ($equipment === NULL) {
			return '';
		}

		return $equipment->name();
	}

	/** Get the equipment the user used to incur the Charge */
	public function equipment() : ?Equipment {
		return $this->equipment;
	}

	/** Set the equipment the user used to incur the Charge */
	public function set_equipment(Equipment $equipment) : self {
		$this->equipment = $equipment;
		$this->equipment_id = $equipment->id();
		return $this;
	}

	/** Get the time of this charge */
	public function time() : string {
		return $this->time;
	}

	/** Set the time of this charge */
	public function set_time(string $time) : self {
		$this->time = $time;
		return $this;
	}

	/** Get the amount of this charge */
	public function amount() : string {
		return $this->amount;
	}

	/** Set the amount of this charge */
	public function set_amount(string $amount) : self {
		$this->amount = $amount;
		return $this;
	}

	/** Get the policy for this charge */
	public function charge_policy() : string {
		return ChargePolicy::name_for_policy($this->charge_policy_id);
	}

	/**
	 * Get the id of the charge policy in effect during the creation of the
	 * Charge
	 */
	public function charge_policy_id() : int {
		return $this->charge_policy_id;
	}

	/**
	 * Set the id of the charge policy in effect during the creation of the
	 * Charge
	 */
	public function set_charge_policy_id(int $charge_policy_id) : self {
		$this->charge_policy_id = $charge_policy_id;
		return $this;
	}

	/** Get the charge rate in effect during the creation of the Charge */
	public function charge_rate() : string {
		return $this->charge_rate;
	}

	/** Set the charge rate in effect during the creation of the Charge */
	public function set_charge_rate(string $charge_rate) : self {
		$this->charge_rate = $charge_rate;
		return $this;
	}

	/**
	 * Get duration in seconds of the activation session that resulted in the
	 * creation of this Charge
	 */
	public function charged_time() : int {
		return $this->charged_time;
	}

	/**
	 * Set duration in seconds of the activation session that resulted in the
	 * creation of this Charge
	 */
	public function set_charged_time(int $charged_time) : self {
		$this->charged_time = $charged_time;
		return $this;
	}
}
