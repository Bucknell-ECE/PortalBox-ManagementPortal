<?php

namespace Portalbox\Model;

use Portalbox\Entity\User;
use Portalbox\Model\Entity\User as PDOAwareUser;
use Portalbox\Exception\DatabaseException;

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
		$connection = $this->connection();
		$sql = 'INSERT INTO users (name, email, comment, role_id, is_active) VALUES (:name, :email, :comment, :role_id, :is_active)';
		$query = $connection->prepare($sql);

		$query->bindValue(':name', $user->name());
		$query->bindValue(':email', $user->email());
		$query->bindValue(':comment', $user->comment());
		$query->bindValue(':is_active', $user->is_active(), PDO::PARAM_BOOL);
		$query->bindValue(':role_id', $user->role()->id(), PDO::PARAM_INT);

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
		$query->bindValue(':id', $id, PDO::PARAM_INT);
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
		$connection = $this->connection();
		$sql = 'UPDATE users SET name = :name, email = :email, comment = :comment, role_id = :role_id, is_active = :is_active WHERE id = :id';
		$query = $connection->prepare($sql);

		$query->bindValue(':id', $user->id(), PDO::PARAM_INT);
		$query->bindValue(':name', $user->name());
		$query->bindValue(':email', $user->email());
		$query->bindValue(':comment', $user->comment());
		$query->bindValue(':is_active', $user->is_active(), PDO::PARAM_BOOL);
		$query->bindValue(':role_id', $user->role()->id(), PDO::PARAM_INT);

		if($query->execute()) {
			$user = (new PDOAwareUser($this->connection()))
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
			$connection = $this->connection();
			$sql = 'DELETE FROM users WHERE id = :id';
			$query = $connection->prepare($sql);
			$query->bindValue(':id', $id, PDO::PARAM_INT);
			if(!$query->execute()) {
				throw new DatabaseException($connection->errorInfo()[2]);
			}
		}

		return $user;
	}
}