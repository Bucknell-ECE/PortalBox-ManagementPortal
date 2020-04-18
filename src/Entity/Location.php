<?php

namespace Portalbox\Entity;

/**
 * Location represents a somewhere Equipment is located.
 * 
 * While a Makerspace might have only a single location, implementors like
 * a University might have multiple locations across campus with equipment
 * that they wish to outfit with Portal boxes as a single campus wide system.
 * 
 * @package Portalbox\Entity
 */
class Location extends AbstractEntity {

	/**
	 * The name of this location
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * Get the name of this location
	 *
	 * @return string - the name of the location
	 */
	public function name() : string {
		return $this->name;
	}

	/**
	 * Set the name of this location
	 *
	 * @param string name - the name for this location
	 * @return Location - returns this in order to support fluent syntax.
	 */
	public function set_name(string $name) : Location {
		$this->name = $name;
		return $this;
	}

}
