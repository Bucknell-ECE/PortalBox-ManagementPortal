<?php

namespace Portalbox\Type;

/**
 * A badge level a user can achieve by using equipment for the number of uses
 */
class BadgeLevel {
	use \Portalbox\Trait\HasIdProperty;

	/**
	 * The name of the badge
	 */
	protected string $name = '';

	/**
	 * The name of the image file used to represent the badge
	 */
	protected string $image = '';

	/**
	 * The id of the badge rule
	 */
	protected int $badge_rule_id = -1;

	/**
	 * The number of equipment uses to achieve this level
	 */
	protected int $uses = -1;

	/**
	 * Get the name of the image file used to represent the badge
	 */
	public function image(): string {
		return $this->image;
	}

	/**
	 * Set the name of the image file used to represent the badge
	 */
	public function set_image(string $image): self {
		$this->image = $image;
		return $this;
	}

	/**
	 * Get the name of the badge
	 */
	public function name(): string {
		return $this->name;
	}

	/**
	 * Set the name of the badge
	 */
	public function set_name(string $name): self {
		$this->name = $name;
		return $this;
	}

	/**
	 * Get the id of the badge rule
	 */
	public function badge_rule_id(): int {
		return $this->badge_rule_id;
	}

	/**
	 * Set the id of the badge rule
	 */
	public function set_badge_rule_id(int $id): self {
		$this->badge_rule_id = $id;
		return $this;
	}

	/**
	 * Get the number of equipment uses to achieve this level
	 */
	public function uses(): int {
		return $this->uses;
	}

	/**
	 * Set the number of equipment uses to achieve this level
	 */
	public function set_uses(int $uses): self {
		$this->uses = $uses;
		return $this;
	}
}