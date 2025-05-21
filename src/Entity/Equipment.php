<?php

namespace Portalbox\Entity;

use InvalidArgumentException;

/**
 * Equipment represents a machine connected to a Portalbox.
 */
class Equipment {
	use \Portalbox\Trait\HasIdProperty;

	/** This user's name */
	protected string $name = '';

	/** This equipment's type id */
	protected int $type_id = -1;

	/** This equipment's type */
	protected ?EquipmentType $type = NULL;

	/** The MAC address of the portalbox the equipment is connected to */
	protected ?string $mac_address = NULL;

	/** The id for the location where the equipment is located */
	protected int $location_id = -1;

	/** The location where the equipment is located */
	protected ?Location $location = NULL;

	/**
	 * The default maximum duration of an activation session for this
	 * equipment in seconds
	 */
	protected int $timeout = 0;

	/** Whether the equipment is in service */
	protected bool $is_in_service = false;

	/** Whether the equipment is in use */
	protected $is_in_use = false;

	/** The time this equipment has been activated */
	protected int $service_minutes = 0;

	/** The ip Address of the box */
	protected ?string $ip_address = NULL;

	/** Get the name of this equipment */
	public function name() : string {
		return $this->name;
	}

	/**
	 * Set the name of this equipment
	 *
	 * @throws InvalidArgumentException  if the parameter is the empty string
	 */
	public function set_name(string $name) : self {
		if($name === '') {
			throw new InvalidArgumentException('You must specify the equipment\'s name');
		}

		$this->name = $name;
		return $this;
	}

	/** Get the equipment's type id */
	public function type_id() : int {
		return $this->type_id;
	}

	/** Set the equipment's type id */
	public function set_type_id(int $type_id) : self {
		$this->type_id = $type_id;
		$this->type = NULL;
		return $this;
	}

	/** Get the equipment's type */
	public function type() : ?EquipmentType {
		return $this->type;
	}

	/** Set the equipment's type */
	public function set_type(EquipmentType $type) : self {
		$this->type = $type;
		$this->type_id = $type->id();
		return $this;
	}

	/** Get the MAC address of the portalbox this equipment is connected to */
	public function mac_address() : ?string {
		return $this->mac_address;
	}

	/**
	 * Set the MAC address of the portalbox this equipment is connected to
	 *
	 * @throws InvalidArgumentException  if the parameter is not a valid MAC
	 *     address.
	 */
	public function set_mac_address(?string $mac_address) : self {
		if(is_null($mac_address)) {
			$this->mac_address = NULL;
			return $this;
		}

		if(!preg_match('/^([0-9A-Fa-f]{2}[:-]?){5}([0-9A-Fa-f]{2})$/', $mac_address)) {
			throw new InvalidArgumentException('The specified MAC Address must be valid');
		}

		$this->mac_address = strtolower(str_replace(array('-', ':'), '', $mac_address));
		return $this;
	}

	/** Get the equipment's location id */
	public function location_id() : int {
		return $this->location_id;
	}

	/** Set the equipment's location id */
	public function set_location_id(int $location_id) : self {
		$this->location_id = $location_id;
		$this->location = NULL;
		return $this;
	}

	/** Get the equipment's location */
	public function location() : ?Location {
		return $this->location;
	}

	/** Set the equipment's location */
	public function set_location(Location $location) : self {
		$this->location = $location;
		$this->location_id = $location->id();
		return $this;
	}

	/** Get the equipment's timeout */
	public function timeout() : int {
		return $this->timeout;
	}

	/** Set the equipment's timeout */
	public function set_timeout(int $timeout) : self {
		$this->timeout = $timeout;
		return $this;
	}

	/** Get whether the equipment is in service */
	public function is_in_service() : bool {
		return $this->is_in_service;
	}

	/** Set whether the equipment is in service */
	public function set_is_in_service(bool $is_in_service) : self {
		$this->is_in_service = $is_in_service;
		return $this;
	}

	/** Get whether the equipment is in use */
	public function is_in_use() : bool {
		return $this->is_in_use;
	}

	/** Set whether the equipment is in use */
	public function set_is_in_use(bool $is_in_use) : self {
		$this->is_in_use = $is_in_use;
		return $this;
	}

	/** Get the equipment's service minutes */
	public function service_minutes() : int {
		return $this->service_minutes;
	}

	/** Set the equipment's service minutes */
	public function set_service_minutes(int $service_minutes) : self {
		$this->service_minutes = $service_minutes;
		return $this;
	}

	/** Get the ip address */
	public function ip_address() : ?string {
		return $this->ip_address;
	}

	/** Set the ip address */
	public function set_ip_address(?string $ip_address) : self {
		$this->ip_address = $ip_address;
		return $this;
	}

}
