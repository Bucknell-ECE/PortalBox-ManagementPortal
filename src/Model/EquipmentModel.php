<?php

namespace Portalbox\Model;

use Portalbox\Entity\Equipment;
use Portalbox\Exception\DatabaseException;
use Portalbox\Model\Entity\Equipment as PDOAwareEquipment;
use Portalbox\Query\EquipmentQuery;

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
			return $equipment
					->set_id($connection->lastInsertId('equipment_id_seq'))
					->set_is_in_use(false);
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
		
		$sql = 'SELECT e.id, e.name, e.type_id, e.mac_address, e.location_id, e.timeout, e.in_service, iu.equipment_id IS NOT NULL AS in_use, e.service_minutes, e.ip_address FROM equipment AS e LEFT JOIN in_use AS iu ON e.id = iu.equipment_id WHERE e.id = :id';
		$query = $connection->prepare($sql);
		$query->bindValue(':id', $id, PDO::PARAM_INT);
		if($query->execute()) {
			
			if($data = $query->fetch(PDO::FETCH_ASSOC)) {
				
				return $this->buildEquipmentFromArray($data);
				
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
			
			// fill in readonly fields...
			$sql = 'SELECT iu.equipment_id IS NOT NULL AS in_use, e.service_minutes FROM equipment AS e LEFT JOIN in_use AS iu ON e.id = iu.equipment_id WHERE e.id = :id';
			$query = $connection->prepare($sql);
			$query->bindValue(':id', $equipment->id(), PDO::PARAM_INT);
			if($query->execute()) {
				if($data = $query->fetch(PDO::FETCH_ASSOC)) {
					$equipment
						->set_is_in_use($data['in_use'])
						->set_service_minutes($data['service_minutes']);
				} else {
					return null;
				}
			} else {
				throw new DatabaseException($connection->errorInfo()[2]);
			}

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

	/**
	 * Search for Equipment
	 * 
	 * @param EquipmentQuery query - the search query to perform
	 * @throws DatabaseException - when the database can not be queried
	 * @return Equipment[]|null - a list of equipment which match the search query
	 */
	public function search(EquipmentQuery $query) : ?array {
		if(NULL === $query) {
			// no query... bail
			return NULL;
		}

		$connection = $this->configuration()->readonly_db_connection();

		$sql = 'SELECT e.id, e.name, e.type_id, e.mac_address, e.location_id, e.timeout, e.in_service, iu.equipment_id IS NOT NULL AS in_use, e.service_minutes, e.ip_address FROM equipment AS e JOIN equipment_types AS t ON e.type_id = t.id JOIN locations AS l ON e.location_id = l.id LEFT JOIN in_use AS iu ON e.id = iu.equipment_id';

		$where_clause_fragments = array();
		$parameters = array();
		if(NULL !== $query->location_id()) {
			$where_clause_fragments[] = 'l.id = :location';
			$parameters[':location'] = $query->location_id();
		} else if(NULL !== $query->location()) {
			$where_clause_fragments[] = 'l.name = :location';
			$parameters[':location'] = $query->location();
		}
		if(NULL !== $query->type()) {
			$where_clause_fragments[] = 't.name = :type';
			$parameters[':type'] = $query->type();
		}
		if($query->include_out_of_service()) {
			// do nothing i.e. do not filter for in service only
		} else {
			$where_clause_fragments[] = 'e.in_service = 1';
		}
		
		if(0 < count($where_clause_fragments)) {
			$sql .= ' WHERE ';
			$sql .= join(' AND ', $where_clause_fragments);
		}
		$sql .= ' ORDER BY l.name, t.name, e.name';
		
		$statement = $connection->prepare($sql);
		// run search
		foreach($parameters as $k => $v) {
			$statement->bindValue($k, $v);
		}

		if($statement->execute()) {
			$data = $statement->fetchAll(PDO::FETCH_ASSOC);
			if(FALSE !== $data) {
				return $this->buildEquipmentFromArrays($data);
			} else {
				return null;
			}
		} else {
			throw new DatabaseException($connection->errorInfo()[2]);
		}
	}

	private function buildEquipmentFromArray(array $data) : Equipment {
		return (new PDOAwareEquipment($this->configuration()))
					->set_id($data['id'])
					->set_name($data['name'])
					->set_type_id($data['type_id'])
					->set_mac_address($data['mac_address'])
					->set_location_id($data['location_id'])
					->set_timeout($data['timeout'])
					->set_is_in_service($data['in_service'])
					->set_is_in_use($data['in_use'])
					->set_service_minutes($data['service_minutes'])
					->set_ip_address($data['ip_address']);
	}

	private function buildEquipmentFromArrays(array $data) : array {
		$equipment = [];

		foreach($data as $datum) {
			$equipment[] = $this->buildEquipmentFromArray($datum);
		}

		return $equipment;
	}
}