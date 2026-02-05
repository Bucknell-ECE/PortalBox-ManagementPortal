<?php

namespace Portalbox\Type;

use InvalidArgumentException;
use Portalbox\Enumeration\ChargePolicy;

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
	protected ?string $charge_rate = null;

	/** The Charge policy for this equipment type */
	protected ChargePolicy $charge_policy = ChargePolicy::MANUALLY_ADJUSTED;

	/** Whether this equipment type allows proxy cards */
	protected bool $allow_proxy = false;

	/** Get the name of this equipment type */
	public function name(): string {
		return $this->name;
	}

	/** Set the name of this equipment type */
	public function set_name(string $name): self {
		if ($name === '') {
			throw new InvalidArgumentException('You must specify the equipment type\'s name');
		}

		$this->name = $name;
		return $this;
	}

	/** Get whether this equipment type requires training */
	public function requires_training(): bool {
		return $this->requires_training;
	}

	/** Set whether this equipment type requires training */
	public function set_requires_training(bool $requires_training): self {
		$this->requires_training = $requires_training;
		return $this;
	}

	/** Get the charge rate this equipment type */
	public function charge_rate(): ?string {
		return $this->charge_rate;
	}

	/** Set the charge rate of this equipment type */
	public function set_charge_rate(?string $charge_rate): self {
		if (null === $charge_rate || $charge_rate !== '') {
			$this->charge_rate = $charge_rate;
			return $this;
		}

		throw new InvalidArgumentException('Charge rate must be a decimal number');
	}

	/** Get the charge policy for this equipment type */
	public function charge_policy(): ChargePolicy {
		return $this->charge_policy;
	}

	/** Set the charge policy id for this equipment type */
	public function set_charge_policy(ChargePolicy $policy): self {
		$this->charge_policy = $policy;
		return $this;
	}

	/** Get whether this equipment type allows proxy cards */
	public function allow_proxy(): bool {
		return $this->allow_proxy;
	}

	/** Set whether this equipment type allows proxy cards */
	public function set_allow_proxy(bool $allow_proxy): self {
		$this->allow_proxy = $allow_proxy;
		return $this;
	}
}
