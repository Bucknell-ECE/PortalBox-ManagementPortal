<?php

namespace Portalbox\Model;

use Portalbox\Exception\DatabaseException;
use Portalbox\Type\CardType;
use PDO;

class CardTypeModel extends AbstractModel {
	public function search(): ?array {
		$connection = $this->configuration()->readonly_db_connection();
		$sql = 'SELECT id, name FROM card_types';
		$statement = $connection->prepare($sql);
		if ($statement->execute()) {
			$data = $statement->fetchAll(PDO::FETCH_ASSOC);
			if (false !== $data) {
				return $this->buildCardTypesFromArrays($data);
			} else {
				return null;
			}
		} else {
			throw new DatabaseException($connection->errorInfo()[2]);
		}
	}

	private function buildCardTypesFromArray(array $data): CardType {
		return (new CardType())
			->set_id($data['id']);
	}

	private function buildCardTypesFromArrays(array $data): array {
		$card_types = [];

		foreach ($data as $datum) {
			$card_types[] = $this->buildCardTypesFromArray($datum);
		}

		return $card_types;
	}
}
