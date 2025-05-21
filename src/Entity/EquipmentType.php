<?php

namespace Portalbox\Entity;

use InvalidArgumentException;

/**
 * Equipment Type binds policy to equipment of the same type
 */
class EquipmentType {
	use \Portalbox\Trait\HasIdProperty;

	/** The name of this equipment type */
	protected string $name = '';

	/** Whether this equipment type requires training */
	protected bool $requires_training = true;

	/** The rate to charge */
	protected ?string $charge_rate = NULL;

	/** The id of the Charge policy for this equipment type */
	protected int $charge_policy_id = ChargePolicy::MANUALLY_ADJUSTED;

	/** Whether this equipment type allows proxy cards */
	protected bool $allow_proxy = false;

	/** Get the name of this equipment type */
	public function name() : string {
		return $this->name;
	}

	/** Set the name of this equipment type */
	public function set_name(string $name) : self {
		if($name === '') {
			throw new InvalidArgumentException('You must specify the equipment type\'s name');
		}

		$this->name = $name;
		return $this;
	}

	/** Get whether this equipment type requires training */
	public function requires_training() : bool {
		return $this->requires_training;
	}

	/** Set whether this equipment type requires training */
	public function set_requires_training(bool $requires_training) : self {
		$this->requires_training = $requires_training;
		return $this;
	}

	/** Get the charge rate this equipment type */
	public function charge_rate() : ?string {
		return $this->charge_rate;
	}

	/** Set the charge rate of this equipment type */
	public function set_charge_rate(?string $charge_rate) : self {
		if(NULL === $charge_rate || $charge_rate !== '') {
			$this->charge_rate = $charge_rate;
			return $this;
		}

		throw new InvalidArgumentException('Charge rate must be a decimal number');
	}

	/**
	 * Get the charge policy id for this equipment type. Must be one of the
	 * public ChargePolicy constants
	 */
	public function charge_policy_id() : int {
		return $this->charge_policy_id;
	}

	/** Get the charge policy for this equipment type */
	public function charge_policy() : string {
		return ChargePolicy::name_for_policy($this->charge_policy_id);
	}

	/** Set the charge policy id for this equipment type
	 *
	 * @throws InvalidArgumentException if the specified id is not one of the
	 *             public constants from ChargePolicy
	 */
	public function set_charge_policy_id(int $charge_policy_id) : self {
		if(ChargePolicy::is_valid($charge_policy_id)) {
			$this->charge_policy_id = $charge_policy_id;
			return $this;
		}

		throw new InvalidArgumentException("charge_policy_id must be one of the public constants from ChargePolicy");
	}

	/** Get whether this equipment type allows proxy cards */
	public function allow_proxy() : bool {
		return $this->allow_proxy;
	}

	/** Set whether this equipment type allows proxy cards */
	public function set_allow_proxy(bool $allow_proxy) : self {
		$this->allow_proxy = $allow_proxy;
		return $this;
	}
}
