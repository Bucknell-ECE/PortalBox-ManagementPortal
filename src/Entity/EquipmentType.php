<?php

namespace Portalbox\Entity;

use InvalidArgumentException;
use ReflectionClass;

/**
 * Equipment Type binds policy to equipment of the same type
 * 
 * @package Portalbox\Entity
 */
class EquipmentType extends AbstractEntity {

	/**
	 * The name of this equipment type
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Whether this equipment type requires training
	 *
	 * @var bool
	 */
	protected $requires_training;

	/**
	 * The rate to charge
	 *
	 * @var string|null
	 */
	protected $charge_rate;

	/**
	 * The id of the Charge policy for this equipment type
	 *
	 * @var 
	 */
	protected $charge_policy_id;

	/**
	 * Whether this equipment type allows proxy cards
	 *
	 * @var bool
	 */
	protected $allow_proxy;

	/**
	 * Get the name of this equipment type
	 *
	 * @return string - the name of the equipment type
	 */
	public function name() : string {
		return $this->name;
	}

	/**
	 * Set the name of this equipment type
	 *
	 * @param string name - the name for this equipment type
	 * @return self
	 */
	public function set_name(string $name) : self {
		if(0 < strlen($name)) {
			$this->name = $name;
			return $this;
		}

		throw new InvalidArgumentException('You must specify the equipment type\'s name');
	}

	/**
	 * Get whether this equipment type requires training
	 *
	 * @return bool - whether this equipment type requires training
	 */
	public function requires_training() : bool {
		return $this->requires_training;
	}

	/**
	 * Set whether this equipment type requires training
	 *
	 * @param bool requires_training - whether this equipment type requires training
	 * @return self
	 */
	public function set_requires_training(bool $requires_training) : self {
		$this->requires_training = $requires_training;
		return $this;
	}

	/**
	 * Get the charge rate this equipment type
	 *
	 * @return string|null - the charge rate of the equipment type
	 */
	public function charge_rate() : ?string {
		return $this->charge_rate;
	}

	/**
	 * Set the charge rate of this equipment type
	 *
	 * @param string|null charge_rate - the charge rate for this equipment type
	 * @return self
	 */
	public function set_charge_rate(?string $charge_rate) : self {
		if(NULL === $charge_rate || 0 < strlen($charge_rate)) {
			$this->charge_rate = $charge_rate;
			return $this;
		}

		throw new InvalidArgumentException('Charge rate must be a decimal number');
	}

	/**
	 * Get the charge policy id for this equipment type
	 *
	 * @return int - the charge policy type for this equipment type. Will be
	 *             one of the public constants in ChargePolicy
	 */
	public function charge_policy_id() : int {
		return $this->charge_policy_id;
	}

	/**
	 * Get the charge policy for this equipment type
	 *
	 * @return string - name for the charge policy
	 */
	public function charge_policy() : string {
		return ChargePolicy::name_for_policy($this->charge_policy_id);
	}

	/**
	 * Set the charge policy id for this equipment type
	 *
	 * @param int charge_policy_id - the charge policy type for this equipment
	 *             type. Must be one of the public constants in ChargePolicy
	 * @throws InvalidArgumentException if the specified id is not one of the
	 *             public constants from ChargePolicy
	 * @return self
	 */
	public function set_charge_policy_id(int $charge_policy_id) : self {
		if(ChargePolicy::is_valid($charge_policy_id)) {
			$this->charge_policy_id = $charge_policy_id;
			return $this;
		}
		
		throw new InvalidArgumentException("charge_policy_id must be one of the public constants from ChargePolicy");
	}

	/**
	 * Get whether this equipment type allows proxy cards
	 *
	 * @return bool - whether this equipment type allows proxy cards
	 */
	public function allow_proxy() : bool {
		return $this->allow_proxy;
	}

	/**
	 * Set whether this equipment type allows proxy cards
	 *
	 * @param bool allow_proxy - whether this equipment type allows proxy cards
	 * @return self
	 */
	public function set_allow_proxy(bool $allow_proxy) : self {
		$this->allow_proxy = $allow_proxy;
		return $this;
	}
}
