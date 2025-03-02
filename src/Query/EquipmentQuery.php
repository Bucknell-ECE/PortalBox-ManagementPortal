<?php

namespace Portalbox\Query;

/**
 * EquipmentQuery presents a standard interface for Equipment search queries
 */
class EquipmentQuery {
	/** Whether to find out of service equipment */
	protected bool $include_out_of_service = false;

	/** Find equipment by named type */
	protected ?string $type = null;

	/** Find equipment in the named location */
	protected ?string $location = null;

	/** Find equipment in the location by id */
	protected ?int $location_id = null;

	/** The ip address of the portalbox attached to the equipment */
	protected ?string $ip_address = null;

	/** Get whether to find out of service equipment */
	public function include_out_of_service(): bool {
		return $this->include_out_of_service;
	}

	/** Set whether to find out of service equipment */
	public function set_include_out_of_service(bool $include_out_of_service): self {
		$this->include_out_of_service = $include_out_of_service;
		return $this;
	}

	/** Get the type */
	public function type(): ?string {
		return $this->type;
	}

	/** Set the name of the equipmen type */
	public function set_type(?string $type): self {
		$this->type = $type;
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
