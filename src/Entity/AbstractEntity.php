<?php

namespace Bucknell\Portalbox\Entity;

/**
 * User represents a User in the system.
 * 
 *	Typically this class is used by requesting the authenticated user instance
 *	from the Session which will be an instance of this class
 * 
 * @package Bucknell\Portalbox\Entity
 */
class AbstractEntity {
	/**
	 * The unique id for this entity
	 *
	 * @var int
	 */
	private $id;

	/**
	 * Get the id of this entity
	 *
	 * @return int - the id of the entity
	 */
	public function id() : int {
		return $this->id;
	}

	/**
	 * Set the unique id of this entity
	 *
	 * @param int id - the unique id for this entity
	 * @return AbstractEntity - returns this in order to support fluent syntax.
	 */
	public function set_id(int $id) : AbstractEntity {
		$this->id = $id;
		return $this;
	}
}
