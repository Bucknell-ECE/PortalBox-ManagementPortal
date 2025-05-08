<?php

namespace Portalbox\Entity;

use InvalidArgumentException;

/**
 * Location represents a somewhere Equipment is located.
 *
 * While a Makerspace might have only a single location, implementors like
 * a University might have multiple locations across campus with equipment
 * that they wish to outfit with Portal boxes as a single campus wide system.
 */
class Location {
	use \Portalbox\Trait\HasIdProperty;

	/** The name of this location */
	protected string $name = '';

	/** Get the name of this location */
	public function name() : string {
		return $this->name;
	}

	/**
	 * Set the name of this location
	 *
	 * @throws InvalidArgumentException if the name is the empty string
	 */
	public function set_name(string $name) : self {
		if($name === '') {
			throw new InvalidArgumentException('You must specify the location\'s name');
		}

		$this->name = $name;
		return $this;
	}
}
