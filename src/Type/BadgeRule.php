<?php

namespace Portalbox\Type;

/**
 * A rule for awarding badges to users
 */
class BadgeRule {
	use \Portalbox\Trait\HasIdProperty;

	/**
	 * The name of the badge rule
	 */
	protected string $name = '';

	/**
	 * The list of ids of the equipment types a user must use to earn the badge
	 */
	protected array $equipment_type_ids = [];

	/**
	 * The levels of this badge rule
	 */
	protected array $levels = [];

	/**
	 * Get the name of the badge rule
	 */
	public function name(): string {
		return $this->name;
	}

	/**
	 * Set the name of the badge rule
	 */
	public function set_name(string $name): self {
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

	/**
	 * Get the levels of this badge rule
	 */
	public function levels(): array {
		return $this->levels;
	}

	/**
	 * Set the levels of this badge rule
	 */
	public function set_levels(array $levels): self {
		$this->levels = $levels;
		return $this;
	}
}