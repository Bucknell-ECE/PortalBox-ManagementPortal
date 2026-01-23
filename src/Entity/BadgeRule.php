<?php

namespace Portalbox\Entity;

use InvalidArgumentException;

/**
 * A rule for awarding badges to users
 */
class BadgeRule {
	use \Portalbox\Trait\HasIdProperty;

	/**
	 * The name of the badge
	 */
	protected string $name = '';

	/**
	 * The list of ids of the equipment types a user must use to earn the badge
	 */
	protected array $equipment_type_ids = [];

	/**
	 * Get the name of the badge
	 */
	public function name(): string {
		return $this->name;
	}

	/**
	 * Set the name of the badge
	 *
	 * @throws InvalidArgumentException if the name is the empty string
	 */
	public function set_name(string $name): self {
		if ($name === '') {
			throw new InvalidArgumentException('You must specify the badge\'s name');
		}

		$this->name = $name;
		return $this;
	}

	/**
	 * Get the list of ids of the equipment types a user must use to earn the
	 * badge
	 */
	public function equipment_type_ids(): array {
		return $this->equipment_type_ids;
	}

	/**
	 * Set the list of ids of the equipment types a user must use to earn the
	 * badge
	 */
	public function set_equipment_type_ids(array $ids): self {
		$this->equipment_type_ids = $ids;
		return $this;
	}
}
