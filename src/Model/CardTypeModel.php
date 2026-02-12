<?php

namespace Portalbox\Model;

use Portalbox\Enumeration\CardType;
use Portalbox\Exception\DatabaseException;
use PDO;

class CardTypeModel extends AbstractModel {
	public function search(): ?array {
		$connection = $this->configuration()->readonly_db_connection();
		$sql = 'SELECT id FROM card_types';
		$statement = $connection->prepare($sql);

		if (!$statement->execute()) {
			throw new DatabaseException($connection->errorInfo()[2]);
		}

		$data = $statement->fetchAll(PDO::FETCH_COLUMN);
		if (false === $data) {
			return [];
		}

		return array_map(
			fn ($id) => CardType::from($id),
			$data
		);
	}
}
