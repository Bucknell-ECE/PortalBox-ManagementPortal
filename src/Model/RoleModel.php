<?php

namespace Portalbox\Model;

use Portalbox\Entity\Role;
use Portalbox\Exception\DatabaseException;

use PDO;

/**
 * RoleModel is our bridge between the database and higher level Entities.
 * 
 * @package Portalbox\Model
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
		$connection = $this->configuration()->writable_db_connection();
		$sql = 'INSERT INTO roles (name, is_system_role, description) VALUES (:name, :is_system_role, :description)';
		$statement = $connection->prepare($sql);

		$statement->bindValue(':name', $role->name());
		$statement->bindValue(':is_system_role', $role->is_system_role(), PDO::PARAM_BOOL);
		$statement->bindValue(':description', $role->description());

		if($connection->beginTransaction()) {
			if($statement->execute()) {
				// Add in permissions
				$role_id = $connection->lastInsertId('roles_id_seq');
	
				$permissions = $role->permissions();
	
				$sql = 'INSERT INTO roles_x_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)';
				$statement = $connection->prepare($sql);
	
				foreach($permissions as $permission_id) {
					$statement->bindValue(':role_id', $role_id, PDO::PARAM_INT);
					$statement->bindValue(':permission_id', $permission_id, PDO::PARAM_INT);
					if(!$statement->execute()) {
						// cancel transaction
						$connection->rollBack();
						return null;
					}
				}

				// all good :. commit
				$connection->commit();
				return $role->set_id($role_id);
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
	 * Read a role by its unique ID
	 *
	 * @param int id - the unique id of the role
	 * @throws DatabaseException - when the database can not be queried
	 * @return Role|null - the role or null if the role could not be found
	 */
	public function read(int $id) : ?Role {
		$connection = $this->configuration()->readonly_db_connection();
		$sql = 'SELECT id, name, is_system_role, description FROM roles WHERE id = :id';
		$statement = $connection->prepare($sql);
		$statement->bindValue(':id', $id, PDO::PARAM_INT);
		if($statement->execute()) {
			if($data = $statement->fetch(PDO::FETCH_ASSOC)) {
				$role = $this->buildRoleFromArray($data);
				
				$sql = 'SELECT permission_id FROM roles_x_permissions WHERE role_id = :role_id';
				$statement = $connection->prepare($sql);
				$statement->bindValue(':role_id', $data['id'], PDO::PARAM_INT);
				if($statement->execute()) {
					return $role->set_permissions(array_map('intval', $statement->fetchAll(PDO::FETCH_COLUMN)));
				}

				// throw exception?
				return null;
			} else {
				return null;
			}
		} else {
			throw new DatabaseException($statement->errorInfo()[2]);
		}
	}

	/**
	 *  Save a modified Role to the database
	 *
	 * @param Role role - the role to save to the database
	 * @throws DatabaseException - when the database can not be queried
	 * @return Role|null - the role or null if the role could not be saved
	 */
	public function update(Role $role) : Role {
		$role_id = $role->id();
		$old_permissions = $this->read($role_id)->permissions();

		$connection = $this->configuration()->writable_db_connection();
		$sql = 'UPDATE roles SET name = :name, is_system_role = :is_system_role, description = :description WHERE id = :id';
		$statement = $connection->prepare($sql);

		$statement->bindValue(':id', $role_id, PDO::PARAM_INT);
		$statement->bindValue(':name', $role->name());
		$statement->bindValue(':is_system_role', $role->is_system_role(), PDO::PARAM_BOOL);
		$statement->bindValue(':description', $role->description());

		if($connection->beginTransaction()) {
			if($statement->execute()) {
				// Permissions... There are three cases:
				//	1) Permissions which were removed -> delete
				//	2) Permissions which were added -> insert
				//	3) Permissions which were not changed -> do nothing

				$permissions = $role->permissions();
				$unchanged_permissions = array_intersect($old_permissions, $permissions);
				$added_permissions = array_diff($permissions, $unchanged_permissions);
				$removed_permissions = array_diff($old_permissions, $unchanged_permissions);
	
				$sql = 'INSERT INTO roles_x_permissions (role_id, permission_id) VALUES (:role_id, :permission_id)';
				$statement = $connection->prepare($sql);
	
				foreach($added_permissions as $permission_id) {
					$statement->bindValue(':role_id', $role_id, PDO::PARAM_INT);
					$statement->bindValue(':permission_id', $permission_id, PDO::PARAM_INT);
					if(!$statement->execute()) {
						// cancel transaction
						$connection->rollBack();
						return null;
					}
				}

				$sql = 'DELETE FROM roles_x_permissions WHERE role_id = :role_id AND permission_id = :permission_id';
				$statement = $connection->prepare($sql);

				foreach($removed_permissions as $permission_id) {
					$statement->bindValue(':role_id', $role_id, PDO::PARAM_INT);
					$statement->bindValue(':permission_id', $permission_id, PDO::PARAM_INT);
					if(!$statement->execute()) {
						// cancel transaction
						$connection->rollBack();
						return null;
					}
				}

				// all good :. commit
				$connection->commit();
				return $role;
			} else {
				throw new DatabaseException($statement->errorInfo()[2]);
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

			$connection = $this->configuration()->writable_db_connection();
			$sql = 'DELETE FROM roles WHERE id = :id';
			$statement = $connection->prepare($sql);
			$statement->bindValue(':id', $id, PDO::PARAM_INT);
			if(!$statement->execute()) {
				throw new DatabaseException($statement->errorInfo()[2]);
			}
		}

		return $role;
	}

	/**
	 * Search for Roles
	 * 
	 * @throws DatabaseException - when the database can not be queried
	 * @return Role[]|null - a list of role which match the search query
	 */
	public function search() : ?array {
		$connection = $this->configuration()->readonly_db_connection();
		$sql = 'SELECT id, name, is_system_role, description FROM roles';
		$statement = $connection->prepare($sql);
		if($statement->execute()) {
			$data = $statement->fetchAll(PDO::FETCH_ASSOC);
			if(FALSE !== $data) {
				return $this->buildRolesFromArrays($data);
			} else {
				return null;
			}
		} else {
			throw new DatabaseException($statement->errorInfo()[2]);
		}
	}

	private function buildRoleFromArray(array $data) : Role {
		return (new Role())
					->set_id($data['id'])
					->set_name($data['name'])
					->set_is_system_role($data['is_system_role'])
					->set_description($data['description']);
	}

	private function buildRolesFromArrays(array $data) : array {
		$roles = array();

		foreach($data as $datum) {
			$roles[] = $this->buildRoleFromArray($datum);
		}

		return $roles;
	}
}