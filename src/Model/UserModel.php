<?php

namespace Bucknell\Portalbox\Model;

use Bucknell\Portalbox\Entity\User;
use Bucknell\Portalbox\Model\Entity\User as PDOAwareUser;
use Bucknell\Portalbox\Exception\DatabaseException;

use PDO;

/**
 * UserModel is our bridge between the database and higher level Entities.
 * 
 * @package Bucknell\Portalbox\Model
 */
class UserModel extends AbstractModel {
	/**
	 * Save a User to the database
	 *
	 * @param User user - the user to save to the database
	 * @throws DatabaseException - when the database can not be queried
	 * @return User|null - the user or null if the user could not be found
	 */
	public function create(User $user) : User {
		$connection = $this->connection();
		$sql = 'INSERT INTO users (name, email, comment, role_id, is_active) VALUES (:name, :email, :comment, :role_id, :is_active)';
		$query = $connection->prepare($sql);

		$query->bindValue(':name', $user->name());
		$query->bindValue(':email', $user->email());
		$query->bindValue(':comment', $user->comment());
		$query->bindValue(':is_active', $user->is_active());
		$query->bindValue(':role_id', $user->role()->id());

		if($query->execute()) {
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
		$connection = $this->connection();
		$sql = 'SELECT id, name, email, comment, is_active, role_id FROM users WHERE id = :id';
		$query = $connection->prepare($sql);
		$query->bindValue(':id', $id);
		if($query->execute()) {
			if($data = $query->fetch(PDO::FETCH_ASSOC)) {
				return (new PDOAwareUser($this->connection()))
					->set_id($data['id'])
					->set_name($data['name'])
					->set_email($data['email'])
					->set_comment($data['comment'])
					->set_is_active($data['is_active'])
					->set_role_id($data['role_id']);
			} else {
				return null;
			}
		} else {
			throw new DatabaseException();
		}
	}

	/**
	 * Delete a user secified by their unique ID
	 *
	 * @param int id - the unique id of the user
	 * @throws DatabaseException - when the database can not be queried
	 * @return User|null - the user or null if the user could not be found
	 */
	public function delete(int $id) : User {
		$user = $this->read($id);

		if(NULL !== $user) {
			$connection = $this->connection();
			$sql = 'DELETE FROM users WHERE id = :id';
			$query = $connection->prepare($sql);
			$query->bindValue(':id', $id);
			if(!$query->execute()) {
				throw new DatabaseException();
			}
		}

		return $user;
	}
}