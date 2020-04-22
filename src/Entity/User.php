<?php

namespace Portalbox\Entity;

/**
 * User represents a User in the system.
 * 
 *	Typically this class is used by requesting the authenticated user instance
 *	from the Session which will be an instance of this class
 * 
 * @package Portalbox\Entity
 */
class User extends AbstractEntity {

	/**
	 * This user's name
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * This user's email address
	 *
	 * @var string
	 */
	protected $email;

	/**
	 * A comment about the user
	 *
	 * @var string|null
	 */
	protected $comment;

	/**
	 * The role id for this user
	 *
	 * @var int
	 */
	protected $role_id;

	/**
	 * The role of the user
	 * 
	 * @var Role|null
	 */
	protected $role;

	/**
	 * Whether this user is active ie can login
	 * 
	 * Log entries have a reference to the user so we can't delete users instead
	 * we change them to inactive
	 *
	 * @var bool
	 */
	protected $is_active;

	/**
	 * A list of equipment type ids the user is authorized for
	 *
	 * @var array<int>|null
	 */
	protected $authorizations;

	/**
	 * Get the name of this user
	 *
	 * @return string - the name of the user
	 */
	public function name() : string {
		return $this->name;
	}

	/**
	 * Set the name of this user
	 *
	 * @param string name - the name for this user
	 * @return User - returns this in order to support fluent syntax.
	 */
	public function set_name(string $name) : User {
		$this->name = $name;
		return $this;
	}

	/**
	 * Get the email address of this user
	 *
	 * @return string - the email address of the user
	 */
	public function email() : string {
		return $this->email;
	}

	/**
	 * Set the email address of this user
	 *
	 * @param string email - the email for this user
	 * @return User - returns this in order to support fluent syntax.
	 */
	public function set_email(string $email) : User {
		$this->email = $email;
		return $this;
	}

	/**
	 * Get the comment for this user
	 *
	 * @return string|null - the comment for the user
	 */
	public function comment() : ?string {
		return $this->comment;
	}

	/**
	 * Set the comment for this user
	 *
	 * @param string comment - the comment for this user
	 * @return User - returns this in order to support fluent syntax.
	 */
	public function set_comment(?string $comment) : User {
		$this->comment = $comment;
		return $this;
	}

	/**
	 * Get this user's role id
	 *
	 * @return int - the user's role id
	 */
	public function role_id() : int {
		return $this->role_id;
	}

	/**
	 * Set the user's role id
	 *
	 * @param int role_id - the role for this user
	 * @return User - returns this in order to support fluent syntax.
	 */
	public function set_role_id(int $role_id) : User {
		$this->role_id = $role_id;
		$this->role = NULL;
		return $this;
	}

	/**
	 * Get this user's role
	 *
	 * @return Role|null - the user's role
	 */
	public function role() : ?Role {
		return $this->role;
	}

	/**
	 * Set the user's role
	 *
	 * @param Role|null role - the role for this user
	 * @return User - returns this in order to support fluent syntax.
	 */
	public function set_role(?Role $role) : User {
		$this->role = $role;
		if(NULL === $role) {
			$this->role_id = -1;
		} else {
			$this->role_id = $role->id();
		}

		return $this;
	}

	/**
	 * Get whether this user is active
	 *
	 * @return bool - whether the user is active
	 */
	public function is_active() : bool {
		return $this->is_active;
	}

	/**
	 * Set whether this user is active
	 *
	 * @param bool is_active - whether this user is active
	 * @return User - returns this in order to support fluent syntax.
	 */
	public function set_is_active(bool $is_active) : User {
		$this->is_active = $is_active;
		return $this;
	}

	/**
	 * Get the authorizations for this user
	 *
	 * @return array<int> - the list of the user's authorizations
	 */
	public function authorizations() : array {
		if(NULL === $this->authorizations) {
			return array();
		}
		return $this->authorizations;
	}

	/**
	 * Set the authorizations for this user
	 *
	 * @param array<int> authorizations - the authorizations for this user
	 * @return User - returns this in order to support fluent syntax.
	 */
	public function set_authorizations(array $authorizations) : User {
		$this->authorizations = $authorizations;
		return $this;
	}
}
