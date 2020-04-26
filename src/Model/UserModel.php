<?php

namespace Portalbox\Model;

use Portalbox\Entity\User;
use Portalbox\Model\Entity\User as PDOAwareUser;
use Portalbox\Exception\DatabaseException;
use Portalbox\Query\UserQuery;

use PDO;

/**
 * UserModel is our bridge between the database and higher level Entities.
 * 
 * @package Portalbox\Model
 */
class UserModel extends AbstractModel {
	/**
	 * Save a newly created User to the database
	 *
	 * @param User user - the user to save to the database
	 * @throws DatabaseException - when the database can not be queried
	 * @return User|null - the user or null if the user could not be saved
	 */
	public function create(User $user) : ?User {
		$connection = $this->configuration()->writable_db_connection();
		$sql = 'INSERT INTO users (name, email, comment, role_id, is_active) VALUES (:name, :email, :comment, :role_id, :is_active)';
		$statement = $connection->prepare($sql);

		$statement->bindValue(':name', $user->name());
		$statement->bindValue(':email', $user->email());
		$statement->bindValue(':comment', $user->comment());
		$statement->bindValue(':is_active', $user->is_active(), PDO::PARAM_BOOL);
		$statement->bindValue(':role_id', $user->role()->id(), PDO::PARAM_INT);

		if($statement->execute()) {
			return $user->set_id($connection->lastInsertId('users_id_seq'));
		} else {
			throw new DatabaseException($connection->errorInfo()[2]);
		}
	}

	/**
	 * Read a user by their unique ID
	 *
	 * @param int id - the unique id of the user
	 * @throws DatabaseException - when the database can not be queried
	 * @return User|null - the user or null if the user could not be found
	 */
	public function read(int $id) : ?User {
		$connection = $this->configuration()->readonly_db_connection();
		$sql = 'SELECT id, name, email, comment, is_active, role_id FROM users WHERE id = :id';
		$statement = $connection->prepare($sql);
		$statement->bindValue(':id', $id, PDO::PARAM_INT);
		if($statement->execute()) {
			if($data = $statement->fetch(PDO::FETCH_ASSOC)) {
				return $this->buildUserFromArray($data);
			} else {
				return null;
			}
		} else {
			throw new DatabaseException($connection->errorInfo()[2]);
		}
	}

	/**
	 * Save a modified User to the database
	 *
	 * @param User user - the user to save to the database
	 * @throws DatabaseException - when the database can not be queried
	 * @return User|null - the user or null if the user could not be saved
	 */
	public function update(User $user) : ?User {
		$connection = $this->configuration()->writable_db_connection();
		$sql = 'UPDATE users SET name = :name, email = :email, comment = :comment, role_id = :role_id, is_active = :is_active WHERE id = :id';
		$statement = $connection->prepare($sql);

		$statement->bindValue(':id', $user->id(), PDO::PARAM_INT);
		$statement->bindValue(':name', $user->name());
		$statement->bindValue(':email', $user->email());
		$statement->bindValue(':comment', $user->comment());
		$statement->bindValue(':is_active', $user->is_active(), PDO::PARAM_BOOL);
		$statement->bindValue(':role_id', $user->role()->id(), PDO::PARAM_INT);

		if($statement->execute()) {
			$user = (new PDOAwareUser($this->configuration()))
				->set_id($user->id())
				->set_name($user->name())
				->set_email($user->email())
				->set_comment($user->comment())
				->set_is_active($user->is_active())
				->set_role_id($user->role()->id());
			
			return $user;
		} else {
			throw new DatabaseException($connection->errorInfo()[2]);
		}
	}

	/**
	 * Delete a user secified by their unique ID
	 *
	 * @param int id - the unique id of the user
	 * @throws DatabaseException - when the database can not be queried
	 * @return User|null - the user or null if the user could not be found
	 */
	public function delete(int $id) : ?User {
		$user = $this->read($id);

		if(NULL !== $user) {
			$connection = $this->configuration()->writable_db_connection();
			$sql = 'DELETE FROM users WHERE id = :id';
			$statement = $connection->prepare($sql);
			$statement->bindValue(':id', $id, PDO::PARAM_INT);
			if(!$statement->execute()) {
				throw new DatabaseException($connection->errorInfo()[2]);
			}
		}

		return $user;
	}

	/**
	 * Search for a User or Users
	 * 
	 * @param UserQuery query - the search query to perform
	 * @throws DatabaseException - when the database can not be queried
	 * @return User[]|null - a list of users which match the search query
	 */
	public function search(UserQuery $query) : ?array {
		if(NULL === $query->email()) {
			// no query... bail
			return NULL;
		}

		$connection = $this->configuration()->readonly_db_connection();
		$sql = 'SELECT id, name, email, comment, is_active, role_id FROM users WHERE email = :email';
		$statement = $connection->prepare($sql);
		$statement->bindValue(':email', $query->email());
		if($statement->execute()) {
			if($data = $statement->fetchAll(PDO::FETCH_ASSOC)) {
				return $this->buildUsersFromArrays($data);
			} else {
				return null;
			}
		} else {
			throw new DatabaseException($connection->errorInfo()[2]);
		}
	}

	private function buildUserFromArray(array $data) : User {
		return (new PDOAwareUser($this->configuration()))
					->set_id($data['id'])
					->set_name($data['name'])
					->set_email($data['email'])
					->set_comment($data['comment'])
					->set_is_active($data['is_active'])
					->set_role_id($data['role_id']);
	}

	private function buildUsersFromArrays(array $data) : array {
		$users = array();

		foreach($data as $datum) {
			$users[] = $this->buildUserFromArray($datum);
		}

		return $users;
	}
}