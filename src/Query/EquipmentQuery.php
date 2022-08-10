<?php

namespace Portalbox\Query;

/**
 * EquipmentQuery presents a standard interface for Equipment search queries
 * 
 * @package Portalbox\Query
 */
class EquipmentQuery {
	/**
	 * Whether to find out of service equipment
	 *
	 * @var bool
	 */
	protected $include_out_of_service;

	/**
	 * Find equipment by named type
	 *
	 * @var string|null
	 */
	protected $type;

	/**
	 * Find equipment in the named location
	 *
	 * @var string|null
	 */
	protected $location;

	/**
	 * Find equipment in the location by id
	 *
	 * @var int|null
	 */
	protected $location_id;

	/**
	 * Create a new Equipment Query by default excluding out of service equipment
	 */
	public function __construct() {
		$this->set_include_out_of_service(false);
	}

	/**
	 * Get whether to find out of service equipment
	 *
	 * @return bool - whether to find out of service equipment
	 */
	public function include_out_of_service() : bool {
		return $this->include_out_of_service;
	}

	/**
	 * Set whether to find out of service equipment
	 *
	 * @param bool include_out_of_service - whether to find out of service equipment
	 * @return self
	 */
	public function set_include_out_of_service(bool $include_out_of_service) : self {
		$this->include_out_of_service = $include_out_of_service;
		return $this;
	}

	/**
	 * Get the type
	 *
	 * @return string|null - the name of the equipment type
	 */
	public function type() : ?string {
		return $this->type;
	}

	/**
	 * Set the name of the equipmen type
	 *
	 * @param string|null type - the name of the equipment type
	 * @return self
	 */
	public function set_type(?string $type) : self {
		$this->type = $type;
		return $this;
	}

	/**
	 * Get the location
	 *
	 * @return string|null - the name of the location
	 */
	public function location() : ?string {
		return $this->location;
	}

	/**
	 * Set the location name
	 *
	 * @param string|null location - the location name
	 * @return self
	 */
	public function set_location(?string $location) : self {
		$this->location = $location;
		return $this;
	}

	/**
	 * Get the location id
	 *
	 * @return int|null - the location id
	 */
	public function location_id() : ?int {
		return $this->location_id;
	}

	/**
	 * Set the location id
	 *
	 * @param int|null location_id - the location id
	 * @return self
	 */
	public function set_location_id(?int $location_id) : self {
		$this->location_id = $location_id;
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