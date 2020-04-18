<?php

namespace Portalbox\Entity;

/**
 * Equipment represents a machine connected to a Portalbox.
 * 
 * @package Portalbox\Entity
 */
class Equipment extends AbstractEntity {

	/**
	 * This user's name
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * This equipment's type id
	 *
	 * @var int
	 */
	protected $type_id;

	/**
	 * This equipment's type
	 *
	 * @var EquipmentType|null
	 */
	protected $type;

	/**
	 * The MAC address of teh portalbox the equipment is connected to
	 *
	 * @var string
	 */
	protected $mac_address;

	/**
	 * The id for the location where the equipment is located
	 *
	 * @var int
	 */
	protected $location_id;

	/**
	 * The location where the equipment is located
	 *
	 * @var Location|null
	 */
	protected $location;

	/**
	 * The default maximum duration of an activation session for this
	 * equipment in seconds
	 * 
	 * @var int
	 */
	protected $timeout;

	/**
	 * Whether the equipment is in service
	 *
	 * @var bool
	 */
	protected $is_in_service;

	/**
	 * The time this equipment has been activated
	 *
	 * @var int
	 */
	protected $service_minutes;

	/**
	 * Get the name of this equipment
	 *
	 * @return string - the name of the equipment
	 */
	public function name() : string {
		return $this->name;
	}

	/**
	 * Set the name of this equipment
	 *
	 * @param string name - the name for this equipment
	 * @return Equipment - returns this in order to support fluent syntax.
	 */
	public function set_name(string $name) : Equipment {
		$this->name = $name;
		return $this;
	}

	/**
	 * Get the equipment's type id
	 *
	 * @return int - the equipment's type id
	 */
	public function type_id() : int {
		return $this->type_id;
	}

	/**
	 * Set the equipment's type id
	 *
	 * @param int type_id - the equipment's type id
	 * @return Equipment - returns this in order to support fluent syntax.
	 */
	public function set_type_id(int $type_id) : Equipment {
		$this->type_id = $type_id;
		$this->type = NULL;
		return $this;
	}

	/**
	 * Get the equipment's type
	 *
	 * @return EquipmentType|null - the equipment's type
	 */
	public function type() : ?EquipmentType {
		return $this->type;
	}

	/**
	 * Set the equipment's type
	 *
	 * @param EquipmentType|null type - the type for the equipment
	 * @return Equipment - returns this in order to support fluent syntax.
	 */
	public function set_type(?EquipmentType $type) : Equipment {
		$this->type = $type;
		if(NULL === $type) {
			$this->type_id = -1;
		} else {
			$this->type_id = $type->id();
		}

		return $this;
	}

	/**
	 * Get the MAC address of the portalbox this equipment is connected to
	 *
	 * @return string - the MAC address of the portalbox this equipment is connected to
	 */
	public function mac_address() : string {
		return $this->mac_address;
	}

	/**
	 * Set the MAC address of the portalbox this equipment is connected to
	 *
	 * @param string mac_address - the MAC address of the portalbox this equipment is connected to
	 * @return Equipment - returns this in order to support fluent syntax.
	 */
	public function set_mac_address(string $mac_address) : Equipment {
		$this->mac_address = $mac_address;
		return $this;
	}

	/**
	 * Get the equipment's location id
	 *
	 * @return int - the equipment's location id
	 */
	public function location_id() : int {
		return $this->location_id;
	}

	/**
	 * Set the equipment's location id
	 *
	 * @param int location_id - the equipment's location id
	 * @return Equipment - returns this in order to support fluent syntax.
	 */
	public function set_location_id(int $location_id) : Equipment {
		$this->location_id = $location_id;
		$this->location = NULL;
		return $this;
	}

	/**
	 * Get the equipment's location
	 *
	 * @return Location|null - the equipment's location
	 */
	public function location() : ?Location {
		return $this->location;
	}

	/**
	 * Set the equipment's location
	 *
	 * @param Location|null location - the location for the equipment
	 * @return Equipment - returns this in order to support fluent syntax.
	 */
	public function set_location(?Location $location) : Equipment {
		$this->location = $location;
		if(NULL === $location) {
			$this->location_id = -1;
		} else {
			$this->location_id = $location->id();
		}

		return $this;
	}

	/**
	 * Get the equipment's timeout
	 *
	 * @return int - the equipment's timeout
	 */
	public function timeout() : int {
		return $this->timeout;
	}

	/**
	 * Set the equipment's timeout
	 *
	 * @param int timeout - the equipment's timeout
	 * @return Equipment - returns this in order to support fluent syntax.
	 */
	public function set_timeout(int $timeout) : Equipment {
		$this->timeout = $timeout;
		return $this;
	}

	/**
	 * Get whether the equipment is in service
	 *
	 * @return bool - whether the equipment is in service
	 */
	public function is_in_service() : bool {
		return $this->is_in_service;
	}

	/**
	 * Set whether the equipment is in service
	 *
	 * @param bool is_in_service - whether the equipment is in service
	 * @return Equipment - returns this in order to support fluent syntax.
	 */
	public function set_is_in_service(bool $is_in_service) : Equipment {
		$this->is_in_service = $is_in_service;
		return $this;
	}

	/**
	 * Get the equipment's service minutes
	 *
	 * @return int - the equipment's service minutes
	 */
	public function service_minutes() : int {
		return $this->service_minutes;
	}

	/**
	 * Set the equipment's service minutes
	 *
	 * @param int service_minutes - the equipment's service minutes
	 * @return Equipment - returns this in order to support fluent syntax.
	 */
	public function set_service_minutes(int $service_minutes) : Equipment {
		$this->service_minutes = $service_minutes;
		return $this;
	}
}