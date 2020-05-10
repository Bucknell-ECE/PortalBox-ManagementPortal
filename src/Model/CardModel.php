<?php

namespace Portalbox\Model;

use Portalbox\Entity\Card;
use PortalBox\Entity\CardType;
use Portalbox\Entity\ProxyCard;
use Portalbox\Entity\ShutdownCard;
use Portalbox\Entity\TrainingCard;
use Portalbox\Entity\UserCard;
use Portalbox\Exception\DatabaseException;

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
				switch($data['type_id']) {
					case CardType::PROXY:
						return (new ProxyCard())->set_id($data['id']);
					case CardType::SHUTDOWN:
						return (new ShutdownCard())->set_id($data['id']);
					case CardType::TRAINING:
						// does this need to be PDO aware?
						return (new TrainingCard())->set_id($data['id'])->set_equipment_type_id($data['equipment_type_id']);
					case CardType::USER:
						// does this need to be PDO aware?
						return(new UserCard())->set_id($data['id'])->set_user_id($data['user_id']);
					default:
						return null;
				}
			} else {
				return null;
			}
		} else {
			throw new DatabaseException($connection->errorInfo()[2]);
		}
	}

	/**
	 * Save a modified Card to the database
	 *
	 * @param Card card - the card to save to the database
	 * @throws DatabaseException - when the database can not be queried
	 * @return Card|null - the card or null if the card could not be saved
	 */
	public function update(Card $card) : ?Card {
		$connection = $this->configuration()->writable_db_connection();

		if(CardType::USER == $card->type_id()) {
			$sql = 'UPDATE users_x_cards SET user_id = :id WHERE card_id = :id';
			$query = $connection->prepare($sql);

			$query->bindValue(':id', $card->id());	//BIGINT
			$query->bindValue(':user_id', $card->user_id(), PDO::PARAM_INT);

			if($query->execute()) {
				return $card;
			}
		}

		if(CardType::TRAINING == $card->type_id()) {
			$sql = 'UPDATE equipment_type_x_cards SET equipment_type_id = :equipment_type_id WHERE card_id = :id';
			$query = $connection->prepare($sql);

			$query->bindValue(':id', $card->id());	//BIGINT
			$query->bindValue(':equipment_type_id', $card->equipment_type_id(), PDO::PARAM_INT);

			if($query->execute()) {
				return $card;
			}
		}
		
		return null;
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
}