<?php

namespace Portalbox\Entity;

use InvalidArgumentException;

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
	 * The MAC address of the portalbox the equipment is connected to
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
	 * Whether the equipment is in use
	 *
	 * @var bool
	 */
	protected $is_in_use;

	/**
	 * The time this equipment has been activated
	 *
	 * @var int
	 */
	protected $service_minutes;

	/**
	 * The ip Address of the box 
	 *
	 * @var string
	 */
	protected $ip_address;

	/**
	 * __construct - create a new defaulted instance
	 */
	public function __construct() {
		$this->set_service_minutes(0);
	}

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
	 * @return self
	 */
	public function set_name(string $name) : self {
		if(0 < strlen($name)) {
			$this->name = $name;
			return $this;
		}

		throw new InvalidArgumentException('You must specify the equipment\'s name');
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
	 * @return self
	 */
	public function set_type_id(int $type_id) : self {
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
	 * @param EquipmentType type - the type for the equipment
	 * @return self
	 */
	public function set_type(EquipmentType $type) : self {
		$this->type = $type;
		$this->type_id = $type->id();
		return $this;
	}

	/**
	 * Get the MAC address of the portalbox this equipment is connected to
	 *
	 * @return string - the MAC address of the portalbox this equipment is connected to
	 */
	public function mac_address() : ?string {
		return $this->mac_address;
	}

	/**
	 * Set the MAC address of the portalbox this equipment is connected to
	 *
	 * @param string mac_address - the MAC address of the portalbox this equipment is connected to
	 * @return self
	 */
	public function set_mac_address(?string $mac_address) : self {
		if(is_null($mac_address)) {
			return $this;
		}
		if(preg_match('/^([0-9A-Fa-f]{2}[:-]?){5}([0-9A-Fa-f]{2})$/', $mac_address)) {
			$this->mac_address = strtolower(str_replace(array('-', ':'), '', $mac_address));
			return $this;
		} else {
			return $this;
		}

		// throw new InvalidArgumentException('The specified MAC Address must be valid');
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
	 * @return self
	 */
	public function set_location_id(int $location_id) : self {
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
	 * @param Location location - the location for the equipment
	 * @return self
	 */
	public function set_location(Location $location) : self {
		$this->location = $location;
		$this->location_id = $location->id();
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
	 * @return self
	 */
	public function set_timeout(int $timeout) : self {
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
	 * @return self
	 */
	public function set_is_in_service(bool $is_in_service) : self {
		$this->is_in_service = $is_in_service;
		return $this;
	}

	/**
	 * Get whether the equipment is in use
	 *
	 * @return bool - whether the equipment is in use
	 */
	public function is_in_use() : bool {
		return $this->is_in_use;
	}

	/**
	 * Set whether the equipment is in use
	 *
	 * @param bool is_in_use - whether the equipment is in use
	 * @return self
	 */
	public function set_is_in_use(bool $is_in_use) : self {
		$this->is_in_use = $is_in_use;
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
	 * @return self
	 */
	public function set_service_minutes(int $service_minutes) : self {
		$this->service_minutes = $service_minutes;
		return $this;
	}

	/**
	 * Get the ip address
	 *
	 * @return string|null - the ip address
	 */
	public function ip_address() : ?string {
		return $this->ip_address;
	}

	/**
	 * Set the ip address
	 *
	 * @param string|null ip_address - the ip address
	 * @return self
	 */
	public function set_ip_address(?string $ip_address) : self {
		$this->ip_address = $ip_address;
		return $this;
	}

}
