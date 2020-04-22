<?php

namespace Portalbox\Model;

use Portalbox\Entity\APIKey;
use Portalbox\Exception\DatabaseException;

use PDO;

/**
 * APIKeyModel is our bridge between the database and higher level Entities.
 * 
 * @package Portalbox\Model
 */
class APIKeyModel extends AbstractModel {
	/**
	 * Save a new api key to the database
	 *
	 * @param APIKey key - the api key to save to the database
	 * @throws DatabaseException - when the database can not be queried
	 * @return APIKey|null - the api key or null if the key could not be saved
	 */
	public function create(APIKey $key) : ?APIKey {
		$connection = $this->configuration()->writable_db_connection();
		$sql = 'INSERT INTO api_keys (name, token) VALUES (:name, :token)';
		$query = $connection->prepare($sql);

		$query->bindValue(':name', $key->name());
		$query->bindValue(':token', $key->token());

		if($query->execute()) {
			return $key->set_id($connection->lastInsertId('api_keys_id_seq'));
		} else {
			throw new DatabaseException($connection->errorInfo()[2]);
		}
	}

	/**
	 * Read an api key by its unique ID
	 *
	 * @param int id - the unique id of the api key
	 * @throws DatabaseException - when the database can not be queried
	 * @return APIKey|null - the api key or null if the key could not be found
	 */
	public function read(int $id) : ?APIKey {
		$connection = $this->configuration()->readonly_db_connection();
		$sql = 'SELECT id, name, token FROM api_keys WHERE id = :id';
		$query = $connection->prepare($sql);
		$query->bindValue(':id', $id, PDO::PARAM_INT);
		if($query->execute()) {
			if($data = $query->fetch(PDO::FETCH_ASSOC)) {
				return (new APIKey())
					->set_id($data['id'])
					->set_name($data['name'])
					->set_token($data['token']);
			} else {
				return null;
			}
		} else {
			throw new DatabaseException($connection->errorInfo()[2]);
		}
	}

	/**
	 * Save a modified api key to the database
	 *
	 * @param APIKey key - the api key to save to the database
	 * @throws DatabaseException - when the database can not be queried
	 * @return APIKey|null - the key or null if the key could not be saved
	 */
	public function update(APIKey $key) : ?APIKey {
		$connection = $this->configuration()->writable_db_connection();
		$sql = 'UPDATE api_keys SET name = :name, token = :token WHERE id = :id';
		$query = $connection->prepare($sql);

		$query->bindValue(':id', $key->id(), PDO::PARAM_INT);
		$query->bindValue(':name', $key->name());
		$query->bindValue(':token', $key->token());

		if($query->execute()) {
			return $key;
		} else {
			throw new DatabaseException($connection->errorInfo()[2]);
		}
	}

	/**
	 * Delete an api key secified by its unique ID
	 *
	 * @param int id - the unique id of the api key
	 * @throws DatabaseException - when the database can not be queried
	 * @return APIKey|null - the api key or null if the key could not be found
	 */
	public function delete(int $id) : ?APIKey {
		$key = $this->read($id);

		if(NULL !== $key) {
			$connection = $this->configuration()->writable_db_connection();
			$sql = 'DELETE FROM api_keys WHERE id = :id';
			$query = $connection->prepare($sql);
			$query->bindValue(':id', $id, PDO::PARAM_INT);
			if(!$query->execute()) {
				throw new DatabaseException($connection->errorInfo()[2]);
			}
		}

		return $key;
	}
}