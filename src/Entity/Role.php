<?php

namespace Bucknell\Portalbox\Entity;

/**
 * Role represents an assignable group of permissions.
 * 
 * Every user is assigned a role and thus has a set of permissions restricting
 * what they can do in the web portal.
 * 
 * @package Bucknell\Portalbox\Entity
 */
class Role extends AbstractEntity {

	/**
	 * The name of this role
	 *
	 * @var string
	 */
	private $name;

	/**
	 * A flag indicating if this role is a system defined/required role
	 *
	 * @var bool
	 */
	private $is_system_role;

	/**
	 * A human readable description of this role
	 *
	 * @var string
	 */
	private $description;

	/**
	 * A list of permissions assigned to this role
	 *
	 * @var array<int>|null
	 */
	private $permissions;

	/**
	 * Get the name of this role
	 *
	 * @return string - the name of the role
	 */
	public function name() : string {
		return $this->name;
	}

	/**
	 * Set the name of this role
	 *
	 * @param string name - the name for this role
	 * @return Role - returns this in order to support fluent syntax.
	 */
	public function set_name(string $name) : Role {
		$this->name = $name;
		return $this;
	}

	/**
	 * Get whether this role is a system role
	 *
	 * @return bool - whether the role is a built in system role
	 */
	public function is_system_role() : bool {
		return $this->is_system_role;
	}

	/**
	 * Set whether this role is a system role
	 *
	 * @param bool is_system_role - whether this role is a system role
	 * @return Role - returns this in order to support fluent syntax.
	 */
	public function set_is_system_role(bool $is_system_role) : Role {
		$this->is_system_role = $is_system_role;
		return $this;
	}

	/**
	 * Get the description of this role
	 *
	 * @return string - the description of the role
	 */
	public function description() : string {
		return $this->description;
	}

	/**
	 * Set the description of this role
	 *
	 * @param string description - the description for this role
	 * @return Role - returns this in order to support fluent syntax.
	 */
	public function set_description(string $description) : Role {
		$this->description = $description;
		return $this;
	}

	/**
	 * Get the permissions for this role
	 *
	 * @return array<int> - the list of the role's permissions
	 */
	public function permissions() : array {
		return $this->permissions;
	}

	/**
	 * Set the permissions for this role
	 *
	 * @param array<int> permissions - the permissions for this role
	 * @return Role - returns this in order to support fluent syntax.
	 */
	public function set_permissions(int $id) : Role {
		$this->permissions = $permissions;
		return $this;
	}

	/**
	 * Determine whether the role has a permission
	 *
	 * @param int permission - the permission to check for
	 * @return bool - true iff the role has the specified permission
	 */
	public function has_permission(int $permission) : bool {
		if(is_array($this->permissions)) {
			return in_array($permission, $this->permissions, TRUE);
		} else {
			return FALSE;
		}
	}
}
