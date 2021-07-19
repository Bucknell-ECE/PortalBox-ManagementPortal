<?php

namespace Portalbox\Model;

use Portalbox\Entity\Card;
use Portalbox\Entity\CardType;
use Portalbox\Entity\ProxyCard;
use Portalbox\Entity\ShutdownCard;
use Portalbox\Entity\TrainingCard;
use Portalbox\Entity\UserCard;

use Portalbox\Exception\DatabaseException;

use Portalbox\Query\CardQuery;
use Portalbox\Query\UserQuery;

use Exception;

use PDO;

/**
 * CardModel is our bridge between the database and higher level Entities.
 * 
 * @package Portalbox\Model
 */
class CardModel extends AbstractModel {
	/**
	 * Save a new Card to the database
	 *
	 * @param Card card - the card to save to the database
	 * @throws DatabaseException - when the database can not be queried
	 * @return Card|null - the card or null if the card could not be saved
	 */
	public function create(Card $card) : ?Card {
		$connection = $this->configuration()->writable_db_connection();
		$sql = 'INSERT INTO cards (id, type_id) VALUES (:id, :type_id)';
		$query = $connection->prepare($sql);

		$query->bindValue(':id', $card->id());	//BIGINT
		$query->bindValue(':type_id', $card->type_id(), PDO::PARAM_INT);

		if($connection->beginTransaction()) {
			if($query->execute()) {
				if(CardType::USER == $card->type_id()) {
					$sql = 'INSERT INTO users_x_cards (user_id, card_id) VALUES (:user_id, :card_id)';
					$query = $connection->prepare($sql);

					$query->bindValue(':card_id', $card->id());	//BIGINT
					$query->bindValue(':user_id', $card->user_id(), PDO::PARAM_INT);

					if(!$query->execute()) {
						// cancel transaction
						$connection->rollBack();
						return null;
					}
				}

				if(CardType::TRAINING == $card->type_id()) {
					$sql = 'INSERT INTO equipment_type_x_cards (equipment_type_id, card_id) VALUES (:equipment_type_id, :card_id)';
					$query = $connection->prepare($sql);

					$query->bindValue(':card_id', $card->id());	//BIGINT
					$query->bindValue(':equipment_type_id', $card->equipment_type_id(), PDO::PARAM_INT);

					if(!$query->execute()) {
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
	public function read(int $id) : ?Card {
		$connection = $this->configuration()->readonly_db_connection();
		$sql = 'SELECT c.id, c.type_id, uxc.user_id, etxc.equipment_type_id FROM cards AS c LEFT JOIN users_x_cards AS uxc ON c.id = uxc.card_id LEFT JOIN equipment_type_x_cards AS etxc ON c.id = etxc.card_id WHERE c.id = :id';
		$query = $connection->prepare($sql);
		$query->bindValue(':id', $id);	//BIGINT
		if($query->execute()) {
			if($data = $query->fetch(PDO::FETCH_ASSOC)) {
				return $this->buildCardsFromArrays(array($data))[0];
			} else {
				return null;
			}
		} else {
			throw new DatabaseException($connection->errorInfo()[2]);
		}
	}

	/**
	 * Delete a card secified by its unique ID
	 *
	 * @param int id - the unique id of the card
	 * @throws DatabaseException - when the database can not be queried
	 * @return Card|null - the card or null if the card could not be found
	 */
	public function delete(int $id) : ?Card {
		$card = $this->read($id);

		if(NULL !== $card) {
			$connection = $this->configuration()->writable_db_connection();

			if($connection->beginTransaction()) {
				if(CardType::USER == $card->type_id()) {
					$sql = 'DELETE FROM users_x_cards WHERE card_id = :id';
					$query = $connection->prepare($sql);

					$query->bindValue(':id', $card->id());	//BIGINT

					if(!$query->execute()) {
						// cancel transaction
						$connection->rollBack();
						return null;
					}
				}

				if(CardType::TRAINING == $card->type_id()) {
					$sql = 'DELETE FROM equipment_type_x_cards WHERE card_id = :id';
					$query = $connection->prepare($sql);

					$query->bindValue(':id', $card->id());

					if(!$query->execute()) {
						// cancel transaction
						$connection->rollBack();
						return null;
					}
				}

				$sql = 'DELETE FROM cards WHERE id = :id';
				$query = $connection->prepare($sql);
				$query->bindValue(':id', $id, PDO::PARAM_INT);
				if($query->execute()) {
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
	 * @return Card[]|null - a list of equipment which match the search query
	 */
	public function search(?CardQuery $query = null) : ?array {
		$connection = $this->configuration()->readonly_db_connection();

		$sql = 'SELECT c.id, c.type_id, uxc.user_id, etxc.equipment_type_id FROM cards AS c LEFT JOIN users_x_cards AS uxc ON c.id = uxc.card_id LEFT JOIN equipment_type_x_cards AS etxc ON c.id = etxc.card_id';

		$where_clause_fragments = array();
		$parameters = array();
		if(NULL !== $query && NULL !== $query->equipment_type_id()) {
			$where_clause_fragments[] = 'etxc.equipment_type_id = :equipment_type_id';
			$parameters[':equipment_type_id'] = $query->equipment_type_id();
		}
		if(NULL !== $query && NULL !== $query->user_id()) {
			$where_clause_fragments[] = 'uxc.user_id = :user_id';
			$parameters[':user_id'] = $query->user_id();
		}
		if(NULL !== $query && NULL !== $query->id()) {
			$where_clause_fragments[] = 'c.id LIKE :id';
			$parameters[':id'] = '%' . $query->id() . '%';
		}
		if(0 < count($where_clause_fragments)) {
			$sql .= ' WHERE ';
			$sql .= join(' AND ', $where_clause_fragments);
		}
		$statement = $connection->prepare($sql);
		// run search
		foreach($parameters as $k => $v) {
			$statement->bindValue($k, $v);
		}

		if($statement->execute()) {
			$data = $statement->fetchAll(PDO::FETCH_ASSOC);
			if(FALSE !== $data) {
				return $this->buildCardsFromArrays($data);
			} else {
				return null;
			}
		} else {
			throw new DatabaseException($connection->errorInfo()[2]);
		}
	}

	private function buildCardFromArray(array $data, array $users, array $equipment_types) : ?Card {
		switch($data['type_id']) {
			case CardType::PROXY:
				return (new ProxyCard())->set_id($data['id']);
			case CardType::SHUTDOWN:
				return (new ShutdownCard())->set_id($data['id']);
			case CardType::TRAINING:
				$equipment_type_id = $data['equipment_type_id'];
				$equipment_type = array_filter($equipment_types,
					function ($e) use ($equipment_type_id) {
						return $e->id() == $equipment_type_id;
					});
				
				$equipment_type = array_pop($equipment_type);
				
				return $card = (new TrainingCard())
					->set_id($data['id'])
					->set_equipment_type_id($data['equipment_type_id'])
					->set_equipment_type($equipment_type);

			case CardType::USER:
				$user_id = $data['user_id'];
				$user = array_filter($users,
					function ($e) use ($user_id) {
						return $e->id() == $user_id;
					});

				$user = array_pop($user);
				
				return(new UserCard())
					->set_id($data['id'])
					->set_user_id($data['user_id'])
					->set_user($user);
			default:
				return null;
		}
	}

	private function buildCardsFromArrays(array $data) : array {
		$cards = [];
		$users = [];
		$roles = [];
		$equipment_types = [];

		$e_model = new EquipmentTypeModel($this->configuration());
		$u_model = new UserModel($this->configuration());
		$r_model = new RoleModel($this->configuration());

		$u_query = new UserQuery();
		
		$equipment_types = $e_model->search();
		$users = $u_model->search($u_query);
		$roles = $r_model->search();

		foreach($users as $user) {
			$r_id = $user->role_id();

			$role = array_filter($roles,
				function ($e) use ($r_id) {
					return $e->id() == $r_id;
				});
			$user->set_role(array_pop($role));
		}

		foreach($data as $datum) {
			$cards[] = $this->buildCardFromArray($datum, $users, $equipment_types);
		}

		return $cards;
	}
}