<?php

namespace Bucknell\Portalbox\Model;

use Bucknell\Portalbox\Entity\Role;
use Bucknell\Portalbox\Model\Entity\Role as PDOAwareRole;
use Bucknell\Portalbox\Exception\DatabaseException;

use PDO;

/**
 * RoleModel is our bridge between the database and higher level Entities.
 * 
 * @package Bucknell\Portalbox\Model
 */
class RoleModel extends AbstractModel {
	/**
	 * Save a Role to the database
	 *
	 * @param Role role - the role to save to the database
	 * @throws DatabaseException - when the database can not be queried
	 * @return Role|null - the role or null if the role could not be saved
	 */
	public function create(Role $role) : ?Role {
		$connection = $this->connection();
		$sql = 'INSERT INTO roles (name, is_system_role, description) VALUES (:name, :is_system_role, :description)';
		$query = $connection->prepare($sql);

		$query->bindValue(':name', $role->name());
		$query->bindValue(':is_system_role', $role->is_system_role(), PDO::PARAM_BOOL);
		$query->bindValue(':description', $role->description());

		if($query->execute()) {
			return $role->set_id($connection->lastInsertId('roles_id_seq'));
		} else {
			throw new DatabaseException($connection->errorInfo()[2]);
		}
	}

	/**
	 * Read a role by its unique ID
	 *
	 * @param int id - the unique id of the role
	 * @throws DatabaseException - when the database can not be queried
	 * @return Role|null - the role or null if the role could not be found
	 */
	public function read(int $id) : ?Role {
		$connection = $this->connection();
		$sql = 'SELECT id, name, is_system_role, description FROM roles WHERE id = :id';
		$query = $connection->prepare($sql);
		$query->bindValue(':id', $id, PDO::PARAM_INT);
		if($query->execute()) {
			if($data = $query->fetch(PDO::FETCH_ASSOC)) {
				return (new PDOAwareRole($this->connection()))
					->set_id($data['id'])
					->set_name($data['name'])
					->set_is_system_role($data['is_system_role'])
					->set_description($data['description']);
			} else {
				return null;
			}
		} else {
			throw new DatabaseException($connection->errorInfo()[2]);
		}
	}

	/**
	 * Delete a role secified by its unique ID
	 *
	 * @param int id - the unique id of the role
	 * @throws DatabaseException - when the database can not be queried
	 * @return Role|null - the role or null if the role could not be deleted
	 */
	public function delete(int $id) : ?Role {
		$role = $this->read($id);

		if(NULL !== $role) {
			if($role->is_system_role()) {
				// system role must not be deleted
				return NULL;
			}

			$connection = $this->connection();
			$sql = 'DELETE FROM roles WHERE id = :id';
			$query = $connection->prepare($sql);
			$query->bindValue(':id', $id, PDO::PARAM_INT);
			if(!$query->execute()) {
				throw new DatabaseException($connection->errorInfo()[2]);
			}
		}

		return $role;
	}
}