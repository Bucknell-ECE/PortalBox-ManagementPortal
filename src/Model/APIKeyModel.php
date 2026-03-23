<?php

namespace Portalbox\Model;

use Portalbox\Enumeration\Permission;
use Portalbox\Exception\DatabaseException;
use Portalbox\Query\APIKeyQuery;
use Portalbox\Type\APIKey;
use PDO;

/**
 * APIKeyModel is our bridge between the database and APIKey instances.
 */
class APIKeyModel extends AbstractModel {
	/**
	 * Save a new api key to the database
	 *
	 * @param APIKey key - the api key to save to the database
	 * @throws DatabaseException - when the database can not be queried
	 * @return APIKey|null - the api key or null if the key could not be saved
	 */
	public function create(APIKey $key): ?APIKey {
		$connection = $this->configuration()->writable_db_connection();
		$sql = 'INSERT INTO api_keys (name, token) VALUES (:name, :token)';
		$statement = $connection->prepare($sql);

		$statement->bindValue(':name', $key->name());
		$statement->bindValue(':token', $key->token());

		if (!$connection->beginTransaction()) {
			throw new DatabaseException($connection->errorInfo()[2]);
		}

		if (!$statement->execute()) {
			$connection->rollBack();	// This is unlikely to succeed but
										// in case it does the transaction
										// lock is released which is a good thing
			throw new DatabaseException($connection->errorInfo()[2]);
		}

		// Add in  permissions
		$api_key_id = $connection->lastInsertId('api_keys_id_seq');

		$permissions = $key->permissions();

		$sql = 'INSERT INTO api_keys_x_permissions (api_key_id, permission_id) VALUES (:api_key_id, :permission_id)';
		$statement = $connection->prepare($sql);

		foreach ($permissions as $permission) {
			$statement->bindValue(':api_key_id', $api_key_id, PDO::PARAM_INT);
			$statement->bindValue(':permission_id', $permission->value, PDO::PARAM_INT);
			if (!$statement->execute()) {
				// cancel transaction
				$connection->rollBack();
				return null; // why aren't we using an exception here?
			}
		}

		// all good :. commit
		$connection->commit();
		return $key->set_id($api_key_id);
	}

	/**
	 * Read an api key by its unique ID
	 *
	 * @param int id - the unique id of the api key
	 * @throws DatabaseException - when the database can not be queried
	 * @return APIKey|null - the api key or null if the key could not be found
	 */
	public function read(int $id): ?APIKey {
		$connection = $this->configuration()->readonly_db_connection();
		$sql = 'SELECT id, name, token FROM api_keys WHERE id = :id';
		$statement = $connection->prepare($sql);
		$statement->bindValue(':id', $id, PDO::PARAM_INT);

		if (!$statement->execute()) {
			throw new DatabaseException($connection->errorInfo()[2]);
		}

		$data = $statement->fetch(PDO::FETCH_ASSOC);
		if (!$data) {
			return null;
		}

		$key = $this->buildAPIKeyFromArray($data);
		$sql = 'SELECT permission_id FROM api_keys_x_permissions WHERE api_key_id = :api_key_id';
		$statement = $connection->prepare($sql);
		$statement->bindValue(':api_key_id', $key->id(), PDO::PARAM_INT);

		if (!$statement->execute()) {
			throw new DatabaseException($connection->errorInfo()[2]);
		}

		return $key->set_permissions(
			array_map(
				fn ($p) => Permission::from($p),
				$statement->fetchAll(PDO::FETCH_COLUMN)
			)
		);
	}

	/**
	 * Save a modified api key to the database
	 *
	 * @param APIKey key - the api key to save to the database
	 * @throws DatabaseException - when the database can not be queried
	 * @return APIKey|null - the key or null if the key could not be saved
	 */
	public function update(APIKey $key): ?APIKey {
		$api_key_id = $key->id();

		$existing_key = $this->read($api_key_id);
		if (!$existing_key) {
			return null;
		}

		$old_permissions = array_map(
			fn ($p) => $p->value,
			$existing_key->permissions()
		);

		$connection = $this->configuration()->writable_db_connection();
		$sql = 'UPDATE api_keys SET name = :name WHERE id = :id';
		$statement = $connection->prepare($sql);

		$statement->bindValue(':id', $api_key_id, PDO::PARAM_INT);
		$statement->bindValue(':name', $key->name());
		// this is weird... we don't update the token because it is immutable

		if (!$connection->beginTransaction()) {
			throw new DatabaseException($connection->errorInfo()[2]);
		}

		if (!$statement->execute()) {
			$connection->rollBack();	// This is unlikely to succeed but
										// in case it does the transaction
										// lock is released which is a good thing
			throw new DatabaseException($connection->errorInfo()[2]);
		}

		$permissions = array_map(
			fn ($p) => $p->value,
			$key->permissions()
		);
		$unchanged_permissions = array_intersect($old_permissions, $permissions);
		$added_permissions = array_diff($permissions, $unchanged_permissions);
		$removed_permissions = array_diff($old_permissions, $unchanged_permissions);

		$sql = 'INSERT INTO api_keys_x_permissions (api_key_id, permission_id) VALUES (:api_key_id, :permission_id)';
		$statement = $connection->prepare($sql);

		foreach ($added_permissions as $permission_id) {
			$statement->bindValue(':api_key_id', $api_key_id, PDO::PARAM_INT);
			$statement->bindValue(':permission_id', $permission_id, PDO::PARAM_INT);
			if (!$statement->execute()) {
				// cancel transaction
				$connection->rollBack();
				return null;
			}
		}

		$sql = 'DELETE FROM api_keys_x_permissions WHERE api_key_id = :api_key_id AND permission_id = :permission_id';
		$statement = $connection->prepare($sql);

		foreach ($removed_permissions as $permission_id) {
			$statement->bindValue(':api_key_id', $api_key_id, PDO::PARAM_INT);
			$statement->bindValue(':permission_id', $permission_id, PDO::PARAM_INT);
			if (!$statement->execute()) {
				// cancel transaction
				$connection->rollBack();
				return null;
			}
		}

		// all good :. commit
		$connection->commit();
		return $key->set_token($existing_key->token());
	}

	/**
	 * Delete an api key specified by its unique ID
	 *
	 * @param int id - the unique id of the api key
	 * @throws DatabaseException - when the database can not be queried
	 * @return APIKey|null - the api key or null if the key could not be found
	 */
	public function delete(int $id): ?APIKey {
		$key = $this->read($id);

		if (null !== $key) {
			$connection = $this->configuration()->writable_db_connection();
			$sql = 'DELETE FROM api_keys WHERE id = :id';
			$statement = $connection->prepare($sql);
			$statement->bindValue(':id', $id, PDO::PARAM_INT);
			if (!$statement->execute()) {
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
	public function search(APIKeyQuery $query): array {
		$connection = $this->configuration()->readonly_db_connection();
		$sql = 'SELECT id, name, token FROM api_keys';

		$where_clause_fragments = [];
		if (null !== $query->token()) {
			$where_clause_fragments[] = 'token = :token';
		}
		if (0 < count($where_clause_fragments)) {
			$sql .= ' WHERE ';
			$sql .= implode(' AND ', $where_clause_fragments);
		}

		$statement = $connection->prepare($sql);
		if (null !== $query->token()) {
			$statement->bindValue(':token', $query->token());
		}

		if (!$statement->execute()) {
			throw new DatabaseException($connection->errorInfo()[2]);
		}

		$data = $statement->fetchAll(PDO::FETCH_ASSOC);
		if ($data === false) {
			return [];
		}

		return array_map(
			fn (array $record) => $this->buildAPIKeyFromArray($record),
			$data
		);
	}

	private function buildAPIKeyFromArray(array $data): APIKey {
		return (new APIKey())
			->set_id($data['id'])
			->set_name($data['name'])
			->set_token($data['token']);
	}
}
