<?php

namespace Portalbox\Entity;

/**
 * User represents a User in the system.
 *
 *	Typically this class is used by requesting the authenticated user instance
 *	from the Session which will be an instance of this class
 */
class User {
	use \Portalbox\Trait\HasIdProperty;

	/** This user's name */
	protected string $name = '';

	/** This user's email address */
	protected string $email = '';

	/** A comment about the user */
	protected ?string $comment = null;

	/** The role id for this user */
	protected int $role_id = -1;

	/** The role of the user */
	protected ?Role $role = null;
	
	/** This user's PIN for authentication */
	protected string $pin = '0000';

	/**
	 * Whether this user is active ie can login
	 *
	 * Log entries have a reference to the user so we can't delete users instead
	 * we change them to inactive
	 */
	protected bool $is_active = false;

	/** A list of equipment type ids the user is authorized for */
	protected ?array $authorizations = null;

	/** Get the name of this user */
	public function name() : string {
		return $this->name;
	}

	/** Set the name of this user */
	public function set_name(string $name) : self {
		$this->name = $name;
		return $this;
	}

	/** Get the email address of this user */
	public function email() : string {
		return $this->email;
	}

	/** Set the email address of this user */
	public function set_email(string $email) : self {
		$this->email = $email;
		return $this;
	}

	/** Get the comment for this user */
	public function comment() : ?string {
		return $this->comment;
	}

	/** Set the comment for this user */
	public function set_comment(?string $comment) : self {
		$this->comment = $comment;
		return $this;
	}

	/** Get this user's role id */
	public function role_id() : int {
		return $this->role_id;
	}

	/** Set the user's role id */
	public function set_role_id(int $role_id) : self {
		$this->role_id = $role_id;
		$this->role = NULL;
		return $this;
	}
	
	/** Get this user's PIN */
	public function pin() : string {
		return $this->pin;
	}

	/** Set this user's PIN */
	public function set_pin(string $pin) : self {
		$this->pin = $pin;
		return $this;
	}

	/** Get the user's role's name */
	public function role_name() : string {
		if(NULL === $this->role) {
			return '';
		} else {
			return $this->role->name();
		}
	}

	/** Get this user's role */
	public function role() : ?Role {
		return $this->role;
	}

	/** Set the user's role */
	public function set_role(?Role $role) : self {
		$this->role = $role;
		if(NULL === $role) {
			$this->role_id = -1;
		} else {
			$this->role_id = $role->id();
		}

		return $this;
	}

	/** Get whether this user is active */
	public function is_active() : bool {
		return $this->is_active;
	}

	/** Set whether this user is active */
	public function set_is_active(bool $is_active) : self {
		$this->is_active = $is_active;
		return $this;
	}

	/** Get the authorizations for this user */
	public function authorizations() : array {
		if(NULL === $this->authorizations) {
			return array();
		}
		return $this->authorizations;
	}

	/**
	 * Set the authorizations for this user
	 *
	 * @param int[] $authorizations  the authorizations for this user
	 */
	public function set_authorizations(array $authorizations) : self {
		// Should check if valid? ie an int that is the id of an equipment type

		$this->authorizations = $authorizations;
		return $this;
	}
}