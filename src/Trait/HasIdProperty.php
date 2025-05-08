<?php

namespace Portalbox\Trait;

/**
 * Use this trait to add an id property to a datatype
 */
trait HasIdProperty {
	/**
	 * The unique id for this entity
	 */
	protected ?int $id = null;

	/**
	 * Get the id of this entity
	 */
	public function id() : ?int {
		return $this->id;
	}

	/**
	 * Set the unique id of this entity
	 */
	public function set_id(?int $id) : self {
		$this->id = $id;
		return $this;
	}
}
