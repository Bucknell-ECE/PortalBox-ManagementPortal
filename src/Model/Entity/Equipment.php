<?php

namespace Portalbox\Model\Entity;

use Portalbox\Entity\Equipment as AbstractEquipment;
use Portalbox\Entity\EquipmentType;
use Portalbox\Entity\Location;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Model\LocationModel;
use PDO;

class Equipment extends AbstractEquipment {
	/**
	 * An open connection to the database
	 * 
	 * @var PDO
	 */
	private $connection;

	/**
	 * @param PDO connection - an open connection to the database
	 */
	public function __construct(PDO $connection) {
		$this->set_connection($connection);
	}

	/**
	 * Get the connection
	 *
	 * @return PDO - an open connection to the database
	 */
	public function connection() : PDO {
		return $this->connection;
	}

	/**
	 * Set the connection
	 *
	 * @param PDO connection - an open connection to the database
	 */
	public function set_connection(PDO $connection) {
		$this->connection = $connection;
	}

	/**
	 * Get the equipment's type
	 *
	 * @return EquipmentType|null - the equipment's type
	 */
	public function type() : ?EquipmentType {
		if(NULL === $this->type) {
			$this->type = (new EquipmentTypeModel($this->connection()))->read($this->type_id());
		}

		return $this->type;
	}

	/**
	 * Get the equipment's location
	 *
	 * @return Location|null - the equipment's location
	 */
	public function Location() : ?Location {
		if(NULL === $this->location) {
			$this->location = (new LocationModel($this->connection()))->read($this->location_id());
		}

		return $this->location;
	}
}
