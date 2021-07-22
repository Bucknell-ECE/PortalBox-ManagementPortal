<?php

namespace Portalbox\Query;

/**
 * UserQuery presents a standard interface for User search queries
 * 
 * @package Portalbox\Query
 */
class UserQuery {
	/**
	 * The email address of the user for which to search
	 *
	 * @var string
	 */
	protected $email;

	/**
	 * The name of the user for which to search
	 * 
	 * @var string
	 */
	protected $name;

	/**
	 * The comment for the user to search for
	 * 
	 * @var string
	 */
	protected $comment;

	/**
	 * The id of the user for which to search
	 * 
	 * @var int
	 */
	protected $id;

	/**
	 * Equipment id to find authorized users for
	 * 
	 * @var int
	 */
	protected $equipment_id;

	/**
	 * Int for determining if inactive users should be included
	 * 
	 * @var int
	 */
	protected $include_inactive;

	/**
	 * Int for role id to search by
	 * 
	 * @var int
	 */
	protected $role_id;

	/**
	 * Get the email address of the user for which to search
	 *
	 * @return string|null - the email address of the user for which to search
	 */
	public function email() : ?string {
		return $this->email;
	}

	/**
	 * Set the email address of the user for which to search
	 *
	 * @param string email - the email address of the user for which to search
	 * @return self
	 */
	public function set_email(string $email) : self {
		$this->email = $email;
		return $this;
	}

	/**
	 * Get the name of the user for which to search
	 * 
	 * @return string|null - the name of the user for which to search
	 */
	public function name() : ?string {
		return $this->name;
	}

	/**
	 * Set the name of the user for which to search
	 *
	 * @param string email - the name of the user for which to search 
	 * @return self
	 */
	public function set_name(string $name) : self {
		$this->name = $name;
		return $this;
	}

	/**
	 * Get the comment of the user for which to search
	 * 
	 * @return string|null
	 */
	public function comment() : ?string {
		return $this->comment;
	}
	/**
	 * Set the comment of the user for which to search
	 *
	 * @param string comment 
	 * @return self
	 */
	public function set_comment(string $comment) : self {
		$this->comment = $comment;
		return $this;
	}

	/**
	 * Get the equipment id to search for authorized users with
	 * 
	 * @return int|null
	 */
	public function equipment_id() : ?int {
		return $this->equipment_id;
	}

	/**
	 * Set the equipment id to search for authorized users with
	 * 
	 * @param int equipment id
	 * @return self
	 */
	public function set_equipment_id(int $equipment_id) : self {
		$this->equipment_id = $equipment_id;
		return $this;
	}

	public function include_inactive() : ?int {
		return $this->include_inactive;
	}

	public function set_include_inactive(int $include_inactive) : self {
		$this->include_inactive = $include_inactive;
		return $this;
	}

	public function role_id() : ?int {
		return $this->role_id;
	}

	public function set_role_id(int $role_id) : self {
		$this->role_id = $role_id;
		return $this;
	}
}