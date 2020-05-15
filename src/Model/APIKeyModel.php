<?php

namespace Portalbox\Model;

use Portalbox\Entity\APIKey;
use Portalbox\Exception\DatabaseException;
use Portalbox\Query\APIKeyQuery;

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
		$statement = $connection->prepare($sql);

		$statement->bindValue(':name', $key->name());
		$statement->bindValue(':token', $key->token());

		if($statement->execute()) {
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
		$statement = $connection->prepare($sql);
		$statement->bindValue(':id', $id, PDO::PARAM_INT);
		if($statement->execute()) {
			if($data = $statement->fetch(PDO::FETCH_ASSOC)) {
				return $this->buildAPIKeyFromArray($data);
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
		$sql = 'UPDATE api_keys SET name = :name WHERE id = :id';
		$statement = $connection->prepare($sql);

		$statement->bindValue(':id', $key->id(), PDO::PARAM_INT);
		$statement->bindValue(':name', $key->name());
		// this is weird... we don't update the token because it is immutable

		if($statement->execute()) {
			// fill in readonly fields...
			$sql = 'SELECT token FROM api_keys WHERE id = :id';
			$query = $connection->prepare($sql);
			$query->bindValue(':id', $key->id(), PDO::PARAM_INT);
			if($query->execute()) {
				if($data = $query->fetch(PDO::FETCH_ASSOC)) {
					$key->set_token($data['token']);
				} else {
					return null;
				}
			} else {
				throw new DatabaseException($connection->errorInfo()[2]);
			}

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
			$statement = $connection->prepare($sql);
			$statement->bindValue(':id', $id, PDO::PARAM_INT);
			if(!$statement->execute()) {
				throw new DatabaseException($connection->errorInfo()[2]);
			}
		}

		return $key;
	}

	/**
	 * Search for an APIKey or APIKeys
	 * 
	 * @param APIKeyQuery query - the search query to perform
	 * @throws DatabaseException - when the database can not be queried
	 * @return APIKey[]|null - a list of api keys which match the search query
	 */
	public function search(APIKeyQuery $query) : ?array {
		if(NULL === $query) {
			// no query... bail
			return NULL;
		}

		$connection = $this->configuration()->readonly_db_connection();
		$sql = 'SELECT id, name, token FROM api_keys';

		$where_clause_fragments = array();
		if(NULL !== $query->token()) {
			$where_clause_fragments[] = 'token = :token';
		}
		if(0 < count($where_clause_fragments)) {
			$sql .= ' WHERE ';
			$sql .= join(' AND ', $where_clause_fragments);
		}

		$statement = $connection->prepare($sql);
		if(NULL !== $query->token()) {
			$statement->bindValue(':token', $query->token());
		}
		
		if($statement->execute()) {
			$data = $statement->fetchAll(PDO::FETCH_ASSOC);
			if(FALSE !== $data) {
				return $this->buildAPIKeysFromArrays($data);
			} else {
				return null;
			}
		} else {
			throw new DatabaseException($connection->errorInfo()[2]);
		}
	}

	private function buildAPIKeyFromArray(array $data) : APIKey {
		return (new APIKey())
			->set_id($data['id'])
			->set_name($data['name'])
			->set_token($data['token']);
	}

	private function buildAPIKeysFromArrays(array $data) : array {
		$keys = array();

		foreach($data as $datum) {
			$keys[] = $this->buildAPIKeyFromArray($datum);
		}

		return $keys;
	}
}