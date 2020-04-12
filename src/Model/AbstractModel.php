<?php

namespace Bucknell\Portalbox\Model;

use PDO;

/**
 * Abstract model is the common foundation on which our object models are built.
 * 
 * @package Bucknell\Portalbox\Model
 */
class AbstractModel {
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
}
