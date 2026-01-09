<?php

namespace Portalbox\Entity;

use InvalidArgumentException;

/**
 * A rule for awarding badges to users
 */
class BadgeRule {
	use \Portalbox\Trait\HasIdProperty;

	/** The name of the badge */
	protected string $name = '';

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
}