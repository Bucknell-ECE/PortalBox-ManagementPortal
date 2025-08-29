<?php

namespace Portalbox\Query;

/**
 * EquipmentQuery presents a standard interface for Equipment search queries
 */
class EquipmentQuery {
	/** Whether to exclude out of service equipment */
	protected bool $exclude_out_of_service = false;

	/** Find equipment by named type */
	protected ?string $type = null;

	/** Find equipment by MAC address */
	protected ?string $mac_address = null;

	/** Find equipment in the named location */
	protected ?string $location = null;

	/** Find equipment in the location by id */
	protected ?int $location_id = null;

	/** The ip address of the portalbox attached to the equipment */
	protected ?string $ip_address = null;

	/** Get whether to exclude out of service equipment */
	public function exclude_out_of_service(): bool {
		return $this->exclude_out_of_service;
	}

	/** Set whether to exclude out of service equipment */
	public function set_exclude_out_of_service(bool $exclude_out_of_service): self {
		$this->exclude_out_of_service = $exclude_out_of_service;
		return $this;
	}

	/** Get the type */
	public function type(): ?string {
		return $this->type;
	}

	/** Set the name of the equipment type */
	public function set_type(?string $type): self {
		$this->type = $type;
		return $this;
	}

	/** Get the MAC address */
	public function mac_address(): ?string {
		return $this->mac_address;
	}

	/**
	 * Set the MAC address
	 *
	 * Note we transform the value in a manner consistent with how addresses are
	 * stored in the database for simplicity later
	 */
	public function set_mac_address(?string $mac_address): self {
		if ($mac_address) {
			$mac_address = strtolower(str_replace(['-', ':'], '', $mac_address));
		}

		$this->mac_address = $mac_address;
		return $this;
	}

	/** Get the location */
	public function location(): ?string {
		return $this->location;
	}

	/** Set the location name */
	public function set_location(?string $location): self {
		$this->location = $location;
		return $this;
	}

	/** Get the location id */
	public function location_id(): ?int {
		return $this->location_id;
	}

	/** Set the location id */
	public function set_location_id(?int $location_id): self {
		$this->location_id = $location_id;
		return $this;
	}

	/** Get the ip address */
	public function ip_address(): ?string {
		return $this->ip_address;
	}

	/** Set the ip address */
	public function set_ip_address(?string $ip_address): self {
		$this->ip_address = $ip_address;
		return $this;
	}
}
