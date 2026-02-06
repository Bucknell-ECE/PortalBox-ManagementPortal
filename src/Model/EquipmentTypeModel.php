<?php

namespace Portalbox\Model;

use InvalidArgumentException;
use Portalbox\Enumeration\ChargePolicy;
use Portalbox\Exception\DatabaseException;
use Portalbox\Type\EquipmentType;
use PDO;

/**
 * EquipmentTypeModel is our bridge between the database and EquipmentType
 * instances.
 */
class EquipmentTypeModel extends AbstractModel {
	public const ERROR_INVALID_SORT_COLUMN = 'Invalid sort field';

	/**
	 * Save a new equipment type to the database
	 *
	 * @param EquipmentType type - the equipment type to save to the database
	 * @throws DatabaseException - when the database can not be queried
	 * @return EquipmentType|null - the equipment type or null if the type could not be saved
	 */
	public function create(EquipmentType $type): ?EquipmentType {
		$connection = $this->configuration()->writable_db_connection();
		$sql = 'INSERT INTO equipment_types (name, requires_training, charge_rate, charge_policy_id, allow_proxy) VALUES (:name, :requires_training, :charge_rate, :charge_policy_id, :allow_proxy)';
		$query = $connection->prepare($sql);

		$query->bindValue(':name', $type->name());
		$query->bindValue(':requires_training', $type->requires_training(), PDO::PARAM_BOOL);
		$query->bindValue(':charge_rate', $type->charge_rate());
		$query->bindValue(':charge_policy_id', $type->charge_policy()->value, PDO::PARAM_INT);
		$query->bindValue(':allow_proxy', $type->allow_proxy(), PDO::PARAM_BOOL);

		if ($query->execute()) {
			return $type->set_id($connection->lastInsertId('equipment_types_id_seq'));
		} else {
			throw new DatabaseException($connection->errorInfo()[2]);
		}
	}

	/**
	 * Read an equipment type by its unique ID
	 *
	 * @param int id - the unique id of the equipment type
	 * @throws DatabaseException - when the database can not be queried
	 * @return EquipmentType|null - the equipment type or null if the type could not be found
	 */
	public function read(int $id): ?EquipmentType {
		$connection = $this->configuration()->readonly_db_connection();
		$sql = 'SELECT id, name, requires_training, charge_rate, charge_policy_id, allow_proxy FROM equipment_types WHERE id = :id';
		$query = $connection->prepare($sql);
		$query->bindValue(':id', $id, PDO::PARAM_INT);
		if ($query->execute()) {
			if ($data = $query->fetch(PDO::FETCH_ASSOC)) {
				return $this->buildEquipmentTypeFromArray($data);
			} else {
				return null;
			}
		} else {
			throw new DatabaseException($connection->errorInfo()[2]);
		}
	}

	/**
	 * Save a modified equipment type to the database
	 *
	 * @param EquipmentType type - the equipment type to save to the database
	 * @throws DatabaseException - when the database can not be queried
	 * @return EquipmentType|null - the equipment type or null if the equipment type could not be saved
	 */
	public function update(EquipmentType $type): ?EquipmentType {
		$connection = $this->configuration()->writable_db_connection();
		$sql = 'UPDATE equipment_types SET name = :name, requires_training = :requires_training, charge_rate = :charge_rate, charge_policy_id = :charge_policy_id, allow_proxy = :allow_proxy WHERE id = :id';
		$query = $connection->prepare($sql);

		$query->bindValue(':id', $type->id(), PDO::PARAM_INT);
		$query->bindValue(':name', $type->name());
		$query->bindValue(':requires_training', $type->requires_training(), PDO::PARAM_BOOL);
		$query->bindValue(':charge_rate', $type->charge_rate());
		$query->bindValue(':charge_policy_id', $type->charge_policy()->value, PDO::PARAM_INT);
		$query->bindValue(':allow_proxy', $type->allow_proxy(), PDO::PARAM_BOOL);

		if ($query->execute()) {
			return $type;
		} else {
			throw new DatabaseException($connection->errorInfo()[2]);
		}
	}

	/**
	 * Delete an equipment type specified by its unique ID
	 *
	 * @param int id - the unique id of the equipment type
	 * @throws DatabaseException - when the database can not be queried
	 * @return EquipmentType|null - the equipment type or null if the equipment type could not be found
	 */
	public function delete(int $id): ?EquipmentType {
		$type = $this->read($id);

		if (null !== $type) {
			$connection = $this->configuration()->writable_db_connection();
			$sql = 'DELETE FROM equipment_types WHERE id = :id';
			$query = $connection->prepare($sql);
			$query->bindValue(':id', $id, PDO::PARAM_INT);
			if (!$query->execute()) {
				throw new DatabaseException($connection->errorInfo()[2]);
			}
		}

		return $type;
	}

	/**
	 * Search for equipment types
	 *
	 * @param ?string $sortColumn  the column to sort results by. Defaults to
	 *      null for do not sort;
	 * @return EquipmentType[]|null - a list of equipment types
	 * @throws DatabaseException - when the database can not be queried
	 * @throws InvalidArgumentException  if the sortColumn is not supported
	 */
	public function search(?string $sortColumn = null): array {
		$sql = 'SELECT id, name, requires_training, charge_policy_id, charge_rate, allow_proxy FROM equipment_types';

		if ($sortColumn) {
			if (!in_array($sortColumn, ['id', 'name'])) {
				throw new InvalidArgumentException(self::ERROR_INVALID_SORT_COLUMN);
			}

			$sql .= ' ORDER BY ';
			$sql .= $sortColumn;
		}

		$connection = $this->configuration()->readonly_db_connection();
		$statement = $connection->prepare($sql);
		if (!$statement->execute()) {
			throw new DatabaseException($connection->errorInfo()[2]);
		}

		$data = $statement->fetchAll(PDO::FETCH_ASSOC);
		if ($data === false) {
			return [];
		}

		return array_map(
			fn (array $record) => $this->buildEquipmentTypeFromArray($record),
			$data
		);
	}

	private function buildEquipmentTypeFromArray(array $data): EquipmentType {
		return (new EquipmentType())
			->set_id($data['id'])
			->set_name($data['name'])
			->set_requires_training($data['requires_training'])
			->set_charge_rate($data['charge_rate'])
			->set_charge_policy(ChargePolicy::from($data['charge_policy_id']))
			->set_allow_proxy($data['allow_proxy']);
	}
}
