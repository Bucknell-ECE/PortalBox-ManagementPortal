<?php

namespace Portalbox\Model\Type;

use Portalbox\Config;
use Portalbox\Model\RoleModel;
use Portalbox\Type\User as AbstractUser;
use Portalbox\Type\Role;

class User extends AbstractUser {
	/**
	 * The configuration to use
	 */
	private Config $configuration;

	/**
	 * The name of this user's role
	 */
	private string $role_name;

	/**
	 * @param Config configuration - the configuration to use
	 */
	public function __construct(Config $configuration) {
		$this->set_configuration($configuration);
	}

	/**
	 * Get the configuration to use
	 *
	 * @return Config - the configuration to use
	 */
	public function configuration(): Config {
		return $this->configuration;
	}

	/**
	 * Set the configuration to use
	 *
	 * @param Config configuration - the configuration to use
	 */
	public function set_configuration(Config $configuration): void {
		$this->configuration = $configuration;
	}

	/**
	 * Get the user's role's name
	 *
	 * @return string - the name of the user's role
	 */
	public function role_name(): string {
		return $this->role_name;
	}

	/**
	 * Set the user's role's name
	 *
	 * @param string name - the name of the user's role
	 */
	public function set_role_name(string $name): User {
		$this->role_name = $name;
		return $this;
	}

	/**
	 * Get this user's role
	 *
	 * @return Role|null - the user's role
	 */
	public function role(): ?Role {
		if (null === $this->role) {
			$this->role = (new RoleModel($this->configuration()))->read($this->role_id());
		}

		return $this->role;
	}
}
