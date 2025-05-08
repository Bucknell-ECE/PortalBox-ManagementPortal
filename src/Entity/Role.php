<?php

namespace Portalbox\Entity;

use InvalidArgumentException;

/**
 * Role represents an assignable group of permissions.
 *
 * Every user is assigned a role and thus has a set of permissions restricting
 * what they can do in the web portal.
 */
class Role {
	use \Portalbox\Trait\HasIdProperty;

	/** The name of this role */
	protected string $name = '';

	/** A flag indicating if this role is a system defined/required role*/
	protected bool $is_system_role = false;

	/** A human readable description of this role */
	protected string $description = '';

	/**
	 * A list of permissions assigned to this role
	 *
	 * @var int[]|null
	 */
	protected ?array $permissions = NULL;

	/** Get the name of this role */
	public function name() : string {
		return $this->name;
	}

	/** Set the name of this role */
	public function set_name(string $name) : self {
		$this->name = $name;
		return $this;
	}

	/** Get whether this role is a system role */
	public function is_system_role() : bool {
		return $this->is_system_role;
	}

	/** Set whether this role is a system role */
	public function set_is_system_role(bool $is_system_role) : self {
		$this->is_system_role = $is_system_role;
		return $this;
	}

	/** Get the description of this role */
	public function description() : string {
		return $this->description;
	}

	/** Set the description of this role */
	public function set_description(string $description) : self {
		$this->description = $description;
		return $this;
	}

	/**
	 * Get the permissions for this role
	 *
	 * @return int[]  the list of the role's permissions
	 */
	public function permissions() : array {
		if(NULL === $this->permissions) {
			return array();
		}
		return $this->permissions;
	}

	/**
	 * Set the permissions for this role
	 *
	 * @param int[] $permissions  the permissions for this role
	 * @throws InvalidArgumentException if any of the  specified permission are
	 *             not not one of the public constants from Permission
	 */
	public function set_permissions(array $permissions) : self {
		foreach($permissions as $permission) {
			if(!Permission::is_valid($permission)) {
				throw new InvalidArgumentException('permission must be one of the public constants from Permission');
			}
		}

		$this->permissions = $permissions;
		return $this;
	}

	/** Determine whether the role has a permission */
	public function has_permission(int $permission) : bool {
		if(is_array($this->permissions)) {
			return in_array($permission, $this->permissions);
		} else {
			return FALSE;
		}
	}

}
