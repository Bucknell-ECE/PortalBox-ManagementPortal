<?php

namespace Portalbox\Model;

use Portalbox\Enumeration\CardType;
use Portalbox\Exception\DatabaseException;
use Portalbox\Model\Type\TrainingCard;
use Portalbox\Model\Type\UserCard;
use Portalbox\Query\CardQuery;
use Portalbox\Type\Card;
use Portalbox\Type\ProxyCard;
use Portalbox\Type\ShutdownCard;
use PDO;

/**
 * CardModel is our bridge between the database and Card instances.
 */
class CardModel extends AbstractModel {
	// we cache the models injected into model aware cards
	private ?EquipmentTypeModel $equipmentTypeModel = null;
	private ?UserModel $userModel = null;

	/**
	 * Save a new Card to the database
	 *
	 * @param Card card - the card to save to the database
	 * @throws DatabaseException - when the database can not be queried
	 * @return Card|null - the card or null if the card could not be saved
	 */
	public function create(Card $card): ?Card {
		$connection = $this->configuration()->writable_db_connection();
		$sql = 'INSERT INTO cards (id, type_id) VALUES (:id, :type_id)';
		$query = $connection->prepare($sql);

		$query->bindValue(':id', $card->id());	//BIGINT
		$query->bindValue(':type_id', $card->type()->value, PDO::PARAM_INT);

		if ($connection->beginTransaction()) {
			if ($query->execute()) {
				if (CardType::USER === $card->type()) {
					$sql = 'INSERT INTO users_x_cards (user_id, card_id) VALUES (:user_id, :card_id)';
					$query = $connection->prepare($sql);

					$query->bindValue(':card_id', $card->id());	//BIGINT
					$query->bindValue(':user_id', $card->user_id(), PDO::PARAM_INT);

					if (!$query->execute()) {
						// cancel transaction
						$connection->rollBack();
						return null;
					}
				}

				if (CardType::TRAINING === $card->type()) {
					$sql = 'INSERT INTO equipment_type_x_cards (equipment_type_id, card_id) VALUES (:equipment_type_id, :card_id)';
					$query = $connection->prepare($sql);

					$query->bindValue(':card_id', $card->id());	//BIGINT
					$query->bindValue(':equipment_type_id', $card->equipment_type_id(), PDO::PARAM_INT);

					if (!$query->execute()) {
						// cancel transaction
						$connection->rollBack();
						return null;
					}
				}


				$connection->commit();
				return $card;
			} else {
				$connection->rollBack();	// This is unlikely to succeed but
											// in case it does the transaction
											// lock is released which is a good thing
				throw new DatabaseException($connection->errorInfo()[2]);
			}
		} else {
			throw new DatabaseException($connection->errorInfo()[2]);
		}
	}

	/**
	 * Read a card by its unique ID
	 *
	 * @param int id - the unique id of the card
	 * @throws DatabaseException - when the database can not be queried
	 * @return Card|null - the card or null if the card could not be found
	 */
	public function read(int $id): ?Card {
		$connection = $this->configuration()->readonly_db_connection();
		$sql = 'SELECT c.id, c.type_id, uxc.user_id, etxc.equipment_type_id FROM cards AS c LEFT JOIN users_x_cards AS uxc ON c.id = uxc.card_id LEFT JOIN equipment_type_x_cards AS etxc ON c.id = etxc.card_id WHERE c.id = :id';
		$query = $connection->prepare($sql);
		$query->bindValue(':id', $id);	//BIGINT
		if (!$query->execute()) {
			throw new DatabaseException($connection->errorInfo()[2]);
		}

		$data = $query->fetch(PDO::FETCH_ASSOC);
		if ($data === false) {
			return null;
		}
		
		return $this->buildCardFromArray($data);
	}

	/**
	 * Delete a card specified by its unique ID
	 *
	 * @param int id - the unique id of the card
	 * @throws DatabaseException - when the database can not be queried
	 * @return Card|null - the card or null if the card could not be found
	 */
	public function delete(int $id): ?Card {
		$card = $this->read($id);

		if (null !== $card) {
			$connection = $this->configuration()->writable_db_connection();

			if ($connection->beginTransaction()) {
				if (CardType::USER === $card->type()) {
					$sql = 'DELETE FROM users_x_cards WHERE card_id = :id';
					$query = $connection->prepare($sql);

					$query->bindValue(':id', $card->id());	//BIGINT

					if (!$query->execute()) {
						// cancel transaction
						$connection->rollBack();
						return null;
					}
				}

				if (CardType::TRAINING === $card->type()) {
					$sql = 'DELETE FROM equipment_type_x_cards WHERE card_id = :id';
					$query = $connection->prepare($sql);

					$query->bindValue(':id', $card->id());

					if (!$query->execute()) {
						// cancel transaction
						$connection->rollBack();
						return null;
					}
				}

				$sql = 'DELETE FROM cards WHERE id = :id';
				$query = $connection->prepare($sql);
				$query->bindValue(':id', $id, PDO::PARAM_INT);
				if ($query->execute()) {
					$connection->commit();
					return $card;
				} else {
					$connection->rollBack();	// This is unlikely to succeed but
												// in case it does the transaction
												// lock is released which is a good thing
					throw new DatabaseException($connection->errorInfo()[2]);
				}
			} else {
				throw new DatabaseException($connection->errorInfo()[2]);
			}
		}

		return $card;
	}

	/**
	 * Search for Cards
	 *
	 * @param CardQuery|null query - the search query to perform
	 * @throws DatabaseException - when the database can not be queried
	 * @return Card[] - the list of cards which match the search query
	 */
	public function search(?CardQuery $query = null): array {
		$connection = $this->configuration()->readonly_db_connection();

		$sql = 'SELECT c.id, c.type_id, uxc.user_id, etxc.equipment_type_id FROM cards AS c LEFT JOIN users_x_cards AS uxc ON c.id = uxc.card_id LEFT JOIN equipment_type_x_cards AS etxc ON c.id = etxc.card_id';

		$where_clause_fragments = [];
		$parameters = [];
		if (null !== $query && null !== $query->equipment_type_id()) {
			$where_clause_fragments[] = 'etxc.equipment_type_id = :equipment_type_id';
			$parameters[':equipment_type_id'] = $query->equipment_type_id();
		}
		if (null !== $query && null !== $query->user_id()) {
			$where_clause_fragments[] = 'uxc.user_id = :user_id';
			$parameters[':user_id'] = $query->user_id();
		}
		if (null !== $query && null !== $query->id()) {
			$where_clause_fragments[] = 'c.id LIKE :id';
			$parameters[':id'] = '%' . $query->id() . '%';
		}
		if (0 < count($where_clause_fragments)) {
			$sql .= ' WHERE ';
			$sql .= implode(' AND ', $where_clause_fragments);
		}
		$statement = $connection->prepare($sql);

		foreach ($parameters as $k => $v) {
			$statement->bindValue($k, $v);
		}

		if (!$statement->execute()) {
			throw new DatabaseException($connection->errorInfo()[2]);
		}

		return array_map(
			fn (array $data) => $this->buildCardFromArray($data),
			$statement->fetchAll(PDO::FETCH_ASSOC)
		);
	}

	private function buildCardFromArray(array $data): ?Card {
		$type = CardType::from($data['type_id']);
		switch ($type) {
			case CardType::PROXY:
				return (new ProxyCard())->set_id($data['id']);
			case CardType::SHUTDOWN:
				return (new ShutdownCard())->set_id($data['id']);
			case CardType::TRAINING:
				if ($this->equipmentTypeModel === null) {
					$this->equipmentTypeModel = new EquipmentTypeModel($this->configuration());
				}

				return $card = (new TrainingCard($this->equipmentTypeModel))
					->set_id($data['id'])
					->set_equipment_type_id($data['equipment_type_id']);
			case CardType::USER:
				if ($this->userModel === null) {
					$this->userModel = new UserModel($this->configuration());
				}

				return(new UserCard($this->userModel))
					->set_id($data['id'])
					->set_user_id($data['user_id']);
			default:
				return null;
		}
	}
}
