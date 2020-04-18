<?php

namespace Portalbox\Model\Entity;

use Portalbox\Entity\User as AbstractUser;
use Portalbox\Entity\Role;
use Portalbox\Model\RoleModel;
use PDO;

class User extends AbstractUser {
	/**
	 * An open connection to the database
	 * 
	 * @var PDO
	 */
	private $connection;

	/**
	 * @param PDO connection - an open connection to the database
	 */
	public function __construct(PDO $connection) {
		$this->set_connection($connection);
	}

	/**
	 * Get the connection
	 *
	 * @return PDO - an open connection to the database
	 */
	public function connection() : PDO {
		return $this->connection;
	}

	/**
	 * Set the connection
	 *
	 * @param PDO connection - an open connection to the database
	 */
	public function set_connection(PDO $connection) {
		$this->connection = $connection;
	}

	/**
	 * Get this user's role
	 *
	 * @return Role|null - the user's role
	 */
	public function role() : ?Role {
		if(NULL === $this->role) {
			$this->role = (new RoleModel($this->connection()))->read($this->role_id());
		}

		return $this->role;
	}
}
