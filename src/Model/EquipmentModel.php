<?php

namespace Portalbox\Model;

use Portalbox\Entity\Equipment;
use Portalbox\Model\Entity\Equipment as PDOAwareEquipment;
use Portalbox\Exception\DatabaseException;

use PDO;

/**
 * EquipmentModel is our bridge between the database and higher level Entities.
 * 
 * @package Portalbox\Model
 */
class EquipmentModel extends AbstractModel {
	/**
	 * Save a newly created Equipment to the database
	 *
	 * @param Equipment equipment - the equipment to save to the database
	 * @throws DatabaseException - when the database can not be queried
	 * @return Equipment|null - the equipment or null if the eqipment could not be saved
	 */
	public function create(Equipment $equipment) : ?Equipment {
		$connection = $this->configuration()->writable_db_connection();
		$sql = 'INSERT INTO equipment (name, type_id, mac_address, location_id, timeout, in_service, service_minutes) VALUES (:name, :type_id, :mac_address, :location_id, :timeout, :in_service, :service_minutes)';
		$query = $connection->prepare($sql);

		$query->bindValue(':name', $equipment->name());
		$query->bindValue(':type_id', $equipment->type_id(), PDO::PARAM_INT);
		$query->bindValue(':mac_address', $equipment->mac_address());
		$query->bindValue(':location_id', $equipment->location_id(), PDO::PARAM_INT);
		$query->bindValue(':timeout', $equipment->timeout(), PDO::PARAM_INT);
		$query->bindValue(':in_service', $equipment->is_in_service(), PDO::PARAM_BOOL);
		$query->bindValue(':service_minutes', $equipment->service_minutes(), PDO::PARAM_INT);

		if($query->execute()) {
			return $equipment->set_id($connection->lastInsertId('equipment_id_seq'));
		} else {
			throw new DatabaseException($connection->errorInfo()[2]);
		}
	}

	/**
	 * Read a equipment by its unique ID
	 *
	 * @param int id - the unique id of the equipment
	 * @throws DatabaseException - when the database can not be queried
	 * @return Equipment|null - the equipment or null if the equipment could not be found
	 */
	public function read(int $id) : ?Equipment {
		$connection = $this->configuration()->readonly_db_connection();
		$sql = 'SELECT id, name, type_id, mac_address, location_id, timeout, in_service, service_minutes FROM equipment WHERE id = :id';
		$query = $connection->prepare($sql);
		$query->bindValue(':id', $id, PDO::PARAM_INT);
		if($query->execute()) {
			if($data = $query->fetch(PDO::FETCH_ASSOC)) {
				return (new PDOAwareEquipment($this->configuration()))
					->set_id($data['id'])
					->set_name($data['name'])
					->set_type_id($data['type_id'])
					->set_mac_address($data['mac_address'])
					->set_location_id($data['location_id'])
					->set_timeout($data['timeout'])
					->set_is_in_service($data['in_service'])
					->set_service_minutes($data['service_minutes']);
			} else {
				return null;
			}
		} else {
			throw new DatabaseException($connection->errorInfo()[2]);
		}
	}

	/**
	 * Save a modified Equipment to the database
	 *
	 * @param Equipment equipment - the equipment to save to the database
	 * @throws DatabaseException - when the database can not be queried
	 * @return Equipment|null - the equipment or null if the equipment could not be saved
	 */
	public function update(Equipment $equipment) : ?Equipment {
		$connection = $this->configuration()->writable_db_connection();
		$sql = 'UPDATE equipment SET name = :name, type_id = :type_id, mac_address = :mac_address, location_id = :location_id, timeout = :timeout, in_service = :in_service, service_minutes = :service_minutes WHERE id = :id';
		$query = $connection->prepare($sql);

		$query->bindValue(':id', $equipment->id(), PDO::PARAM_INT);
		$query->bindValue(':name', $equipment->name());
		$query->bindValue(':type_id', $equipment->type_id(), PDO::PARAM_INT);
		$query->bindValue(':mac_address', $equipment->mac_address());
		$query->bindValue(':location_id', $equipment->location_id(), PDO::PARAM_INT);
		$query->bindValue(':timeout', $equipment->timeout(), PDO::PARAM_INT);
		$query->bindValue(':in_service', $equipment->is_in_service(), PDO::PARAM_BOOL);
		$query->bindValue(':service_minutes', $equipment->service_minutes(), PDO::PARAM_INT);

		if($query->execute()) {
			$equipment = (new PDOAwareEquipment($this->configuration()))
				->set_id($equipment->id())
				->set_name($equipment->name())
				->set_type_id($equipment->type_id())
				->set_mac_address($equipment->mac_address())
				->set_location_id($equipment->location_id())
				->set_timeout($equipment->timeout())
				->set_is_in_service($equipment->is_in_service())
				->set_service_minutes($equipment->service_minutes());
			
			return $equipment;
		} else {
			throw new DatabaseException($connection->errorInfo()[2]);
		}
	}

	/**
	 * Delete an equipment secified by their unique ID
	 *
	 * @param int id - the unique id of the equipment
	 * @throws DatabaseException - when the database can not be queried
	 * @return Equipment|null - the equipment or null if the equipment could not be found
	 */
	public function delete(int $id) : ?Equipment {
		$equipment = $this->read($id);

		if(NULL !== $equipment) {
			$connection = $this->configuration()->writable_db_connection();
			$sql = 'DELETE FROM equipment WHERE id = :id';
			$query = $connection->prepare($sql);
			$query->bindValue(':id', $id, PDO::PARAM_INT);
			if(!$query->execute()) {
				throw new DatabaseException($connection->errorInfo()[2]);
			}
		}

		return $equipment;
	}
}