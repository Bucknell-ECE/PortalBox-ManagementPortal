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

		if($connection->beginTransaction()) {
			if($statement->execute()) {
				// Add in authorizations
				$user_id = $connection->lastInsertId('users_id_seq');
	
				$authorizations = $user->authorizations();
	
				$sql = 'INSERT INTO authorizations (user_id, equipment_type_id) VALUES (:user_id, :equipment_type_id)';
				$statement = $connection->prepare($sql);
	
				foreach($authorizations as $equipment_type_id) {
					$statement->bindValue(':user_id', $user_id, PDO::PARAM_INT);
					$statement->bindValue(':equipment_type_id', $equipment_type_id, PDO::PARAM_INT);
					if(!$statement->execute()) {
						// cancel transaction
						$connection->rollBack();
						return null;
					}
				}

				// all good :. commit
				$connection->commit();
				return $user->set_id($user_id);
			} else {
				$connection->rollBack();	// This is unlikely to succeed but
											// in case it does the transaction
											// lock is released which is a good thing
				throw new DatabaseException($statement->errorInfo()[2]);
			}
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
		$sql = 'SELECT u.id, u.name, u.email, u.comment, u.is_active, u.role_id, r.name AS role FROM users AS u INNER JOIN roles AS r ON u.role_id = r.id WHERE u.id = :id';
		$statement = $connection->prepare($sql);
		$statement->bindValue(':id', $id, PDO::PARAM_INT);
		if($statement->execute()) {
			if($data = $statement->fetch(PDO::FETCH_ASSOC)) {
				$user = $this->buildUserFromArray($data);

				$sql = 'SELECT equipment_type_id FROM authorizations WHERE user_id = :user_id';
				$query = $connection->prepare($sql);
				$query->bindValue(':user_id', $data['id'], PDO::PARAM_INT);
				if($query->execute()) {
					return $user->set_authorizations(array_map('intval', $query->fetchAll(PDO::FETCH_COLUMN)));
				}

				return null;
			} else {
				return null;
			}
		} else {
			throw new DatabaseException($statement->errorInfo()[2]);
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
		$user_id = $user->id();
		$authorizations = $user->authorizations();
		$old_authorizations = $this->read($user_id)->authorizations();

		$connection = $this->configuration()->writable_db_connection();
		$sql = 'UPDATE users SET name = :name, email = :email, comment = :comment, role_id = :role_id, is_active = :is_active WHERE id = :id';
		$statement = $connection->prepare($sql);

		$statement->bindValue(':id', $user->id(), PDO::PARAM_INT);
		$statement->bindValue(':name', $user->name());
		$statement->bindValue(':email', $user->email());
		$statement->bindValue(':comment', $user->comment());
		$statement->bindValue(':is_active', $user->is_active(), PDO::PARAM_BOOL);
		$statement->bindValue(':role_id', $user->role()->id(), PDO::PARAM_INT);

		if($connection->beginTransaction()) {
			if($statement->execute()) {
				$user = (new PDOAwareUser($this->configuration()))
					->set_id($user->id())
					->set_name($user->name())
					->set_email($user->email())
					->set_comment($user->comment())
					->set_is_active($user->is_active())
					->set_role_id($user->role()->id());
				
				// Authorizations... There are three cases:
				//	1) Authorizations which were removed -> delete
				//	2) Authorizations which were added -> insert
				//	3) Authorizations which were not changed -> do nothing

				$unchanged_authorizations = array_intersect($old_authorizations, $authorizations);
				$added_authorizations = array_diff($authorizations, $unchanged_authorizations);
				$removed_authorizations = array_diff($old_authorizations, $unchanged_authorizations);
	
				$sql = 'INSERT INTO authorizations (user_id, equipment_type_id) VALUES (:user_id, :equipment_type_id)';
				$statement = $connection->prepare($sql);
	
				foreach($added_authorizations as $equipment_type_id) {
					$statement->bindValue(':user_id', $user_id, PDO::PARAM_INT);
					$statement->bindValue(':equipment_type_id', $equipment_type_id, PDO::PARAM_INT);
					if(!$statement->execute()) {
						// cancel transaction
						$connection->rollBack();
						return null;
					}
				}

				$sql = 'DELETE FROM authorizations WHERE user_id = :user_id AND equipment_type_id = :equipment_type_id';
				$statement = $connection->prepare($sql);

				foreach($removed_authorizations as $equipment_type_id) {
					$statement->bindValue(':user_id', $user_id, PDO::PARAM_INT);
					$statement->bindValue(':equipment_type_id', $equipment_type_id, PDO::PARAM_INT);
					if(!$statement->execute()) {
						// cancel transaction
						$connection->rollBack();
						return null;
					}
				}

				// all good :. commit
				$connection->commit();
				return $user->set_authorizations($authorizations);
			} else {
				throw new DatabaseException($statement->errorInfo()[2]);
			}
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
				print_r($connection->errorCode());
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
		if(NULL === $query) {
			// no query... bail
			return NULL;
		}

		$connection = $this->configuration()->readonly_db_connection();
		$sql = 'SELECT u.id, u.name, u.email, u.comment, u.is_active, u.role_id, r.name AS role FROM users AS u INNER JOIN roles AS r ON u.role_id = r.id';
		$where_clause_fragments = array();
		$parameters = array();
		$modifier = "";

		if(NULL !== $query->include_inactive()) {
			if($query->include_inactive() === 0) {
				$where_clause_fragments[] = 'u.is_active = :is_active';
				$parameters[':is_active'] = 1;
			}
		} else {
			$where_clause_fragments[] = 'u.is_active = :is_active';
			$parameters[':is_active'] = 1;
		}
		if(NULL !== $query->role_id()) {
			$where_clause_fragments[] = 'role_id = :role_id';
			$parameters[':role_id'] = $query->role_id();
		}
		if(NULL !== $query->email()) {
			$where_clause_fragments[] = 'email = :email';
			$parameters[':email'] = $query->email();
		} elseif(NULL !== $query->name()) {
			$where_clause_fragments[] = 'u.name LIKE :name';
			$parameters[':name'] = '%' . $query->name() . '%';
		} elseif(NULL !== $query->comment()) {
			$where_clause_fragments[] = 'u.comment LIKE :comment';
			$parameters[':comment'] = '%' . $query->comment() . '%';
		} elseif(NULL !== $query->equipment_id()) {
			$sql .= ' INNER JOIN authorizations AS a ON u.id = a.user_id';
			$where_clause_fragments[] = 'a.equipment_type_id = :equipment_id';
			$parameters[':equipment_id'] = $query->equipment_id();
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
				return $this->buildUsersFromArrays($data);
			} else {
				return null;
			}
		} else {
			throw new DatabaseException($statement->errorInfo()[2]);
		}
	}

	private function buildUserFromArray(array $data) : User {
		return (new PDOAwareUser($this->configuration()))
					->set_id($data['id'])
					->set_name($data['name'])
					->set_email($data['email'])
					->set_comment($data['comment'])
					->set_is_active($data['is_active'])
					->set_role_id($data['role_id'])
					->set_role_name($data['role']);
	}

	private function buildUsersFromArrays(array $data) : array {
		$users = array();

		foreach($data as $datum) {
			$users[] = $this->buildUserFromArray($datum);
		}

		return $users;
	}
}