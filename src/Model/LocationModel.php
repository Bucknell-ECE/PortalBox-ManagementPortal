<?php

namespace Portalbox\Model;

use Portalbox\Entity\Location;
use Portalbox\Exception\DatabaseException;

use PDO;

/**
 * LocationModel is our bridge between the database and higher level Entities.
 * 
 * @package Portalbox\Model
 */
class LocationModel extends AbstractModel {
	/**
	 * Save a new Location to the database
	 *
	 * @param Location location - the location to save to the database
	 * @throws DatabaseException - when the database can not be queried
	 * @return Location|null - the location or null if the location could not be saved
	 */
	public function create(Location $location) : ?Location {
		$connection = $this->configuration()->writable_db_connection();
		$sql = 'INSERT INTO locations (name) VALUES (:name)';
		$query = $connection->prepare($sql);

		$query->bindValue(':name', $location->name());

		if($query->execute()) {
			return $location->set_id($connection->lastInsertId('locations_id_seq'));
		} else {
			throw new DatabaseException($connection->errorInfo()[2]);
		}
	}

	/**
	 * Read a location by its unique ID
	 *
	 * @param int id - the unique id of the location
	 * @throws DatabaseException - when the database can not be queried
	 * @return Location|null - the location or null if the location could not be found
	 */
	public function read(int $id) : ?Location {
		$connection = $this->configuration()->readonly_db_connection();
		$sql = 'SELECT id, name FROM locations WHERE id = :id';
		$query = $connection->prepare($sql);
		$query->bindValue(':id', $id, PDO::PARAM_INT);
		if($query->execute()) {
			if($data = $query->fetch(PDO::FETCH_ASSOC)) {
				return $this->buildLocationFromArray($data);
			} else {
				return null;
			}
		} else {
			throw new DatabaseException($connection->errorInfo()[2]);
		}
	}

	/**
	 * Save a modified Location to the database
	 *
	 * @param Location location - the location to save to the database
	 * @throws DatabaseException - when the database can not be queried
	 * @return Location|null - the location or null if the location could not be saved
	 */
	public function update(Location $location) : ?Location {
		$connection = $this->configuration()->writable_db_connection();
		$sql = 'UPDATE locations SET name = :name WHERE id = :id';
		$query = $connection->prepare($sql);

		$query->bindValue(':id', $location->id(), PDO::PARAM_INT);
		$query->bindValue(':name', $location->name());

		if($query->execute()) {
			return $location;
		} else {
			throw new DatabaseException($connection->errorInfo()[2]);
		}
	}

	/**
	 * Delete a location secified by its unique ID
	 *
	 * @param int id - the unique id of the location
	 * @throws DatabaseException - when the database can not be queried
	 * @return Location|null - the location or null if the location could not be found
	 */
	public function delete(int $id) : ?Location {
		$location = $this->read($id);

		if(NULL !== $location) {
			$connection = $this->configuration()->writable_db_connection();
			$sql = 'DELETE FROM locations WHERE id = :id';
			$query = $connection->prepare($sql);
			$query->bindValue(':id', $id, PDO::PARAM_INT);
			if(!$query->execute()) {
				throw new DatabaseException($connection->errorInfo()[2]);
			}
		}

		return $location;
	}

	/**
	 * Search for locations
	 * 
	 * @throws DatabaseException - when the database can not be queried
	 * @return Location[]|null - a list of locations
	 */
	public function search() : ?array {

		$connection = $this->configuration()->readonly_db_connection();

		$sql = 'SELECT id, name FROM locations';
		$statement = $connection->prepare($sql);
		if($statement->execute()) {
			$data = $statement->fetchAll(PDO::FETCH_ASSOC);
			if(FALSE !== $data) {
				return $this->buildLocationsFromArrays($data);
			} else {
				return null;
			}
		} else {
			throw new DatabaseException($connection->errorInfo()[2]);
		}
	}

	private function buildLocationFromArray(array $data) : Location {
		return (new Location())
					->set_id($data['id'])
					->set_name($data['name']);
	}

	private function buildLocationsFromArrays(array $data) : array {
		$locations = array();

		foreach($data as $datum) {
			$locations[] = $this->buildLocationFromArray($datum);
		}

		return $locations;
	}
}