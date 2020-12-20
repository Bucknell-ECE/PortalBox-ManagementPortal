<?php

namespace Portalbox\Entity;

/**
 * AbstractEntity is the base class for the other entities in the system. It
 * "abstracts out" common functionality.
 *
 * @package Portalbox\Entity
 */
class AbstractEntity {
	/**
	 * The unique id for this entity
	 *
	 * @var int
	 */
	protected $id;

	/**
	 * Get the id of this entity
	 *
	 * @return int|null - the id of the entity
	 */
	public function id() : ?int {
		return $this->id;
	}

	/**
	 * Set the unique id of this entity
	 *
	 * @param int id - the unique id for this entity
	 * @return self
	 */
	public function set_id(int $id) : self {
		$this->id = $id;
		return $this;
	}
}
