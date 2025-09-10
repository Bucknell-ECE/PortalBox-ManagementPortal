<?php

namespace Portalbox\Model;

use DateTimeImmutable;
use Portalbox\Exception\DatabaseException;
use PDO;

/**
 * Manage records of when portal boxes were most recently activated
 */
class ActivationModel extends AbstractModel {
	/**
	 * Create an activation record for the equipment with the given id
	 *
	 * @param int $id  the unique id of the equipment being activated
	 * @return bool  true if the record was created
	 * @throws DatabaseException  when the database can not be queried
	 */
	public function create(int $id): bool {
		$connection = $this->configuration()->writable_db_connection();
		$sql = 'INSERT INTO in_use (equipment_id) VALUES (:id)';
		$query = $connection->prepare($sql);
		$query->bindValue(':id', $id);

		if (!$query->execute()) {
			throw new DatabaseException($connection->errorInfo()[2]);
		}

		return true;
	}

	/**
	 * Read an activation record for the equipment with the given id
	 *
	 * @param int $id  the unique id of the activated equipment
	 * @return DateTimeImmutable|null when the equipment was activated
	 * @throws DatabaseException  when the database can not be queried
	 */
	public function read(int $id): ?DateTimeImmutable {
		$connection = $this->configuration()->readonly_db_connection();

		$sql = 'SELECT start_time FROM in_use WHERE equipment_id = :id';
		$query = $connection->prepare($sql);
		$query->bindValue(':id', $id, PDO::PARAM_INT);
		if (!$query->execute()) {
			throw new DatabaseException($connection->errorInfo()[2]);
		}

		$data = $query->fetchColumn();
		if ($data) {
			return new DateTimeImmutable($data);
		}

		return null;
	}

	/**
	 * Delete an activation record for the equipment with the given id
	 *
	 * @param int id  the unique id of the activated equipment
	 * @return DateTimeImmutable|null  when the equipment was activated
	 * @throws DatabaseException  when the database can not be queried
	 */
	public function delete(int $id): ?DateTimeImmutable {
		$start_time = $this->read($id);

		if ($start_time === null) {
			return null;
		}

		$connection = $this->configuration()->writable_db_connection();
		$sql = 'DELETE FROM in_use WHERE equipment_id = :id';
		$query = $connection->prepare($sql);
		$query->bindValue(':id', $id, PDO::PARAM_INT);
		if (!$query->execute()) {
			throw new DatabaseException($connection->errorInfo()[2]);
		}

		return $start_time;
	}
}
