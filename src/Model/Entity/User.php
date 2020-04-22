<?php

namespace Portalbox\Model\Entity;

use Portalbox\Config;
use Portalbox\Entity\User as AbstractUser;
use Portalbox\Entity\Role;
use Portalbox\Model\RoleModel;

class User extends AbstractUser {
	/**
	 * The configuration to use
	 * 
	 * @var Config
	 */
	private $configuration;

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
	public function configuration() : Config {
		return $this->configuration;
	}

	/**
	 * Set the configuration to use
	 *
	 * @param Config configuration - the configuration to use
	 */
	public function set_configuration(Config $configuration) {
		$this->configuration = $configuration;
	}

	/**
	 * Get this user's role
	 *
	 * @return Role|null - the user's role
	 */
	public function role() : ?Role {
		if(NULL === $this->role) {
			$this->role = (new RoleModel($this->configuration()))->read($this->role_id());
		}

		return $this->role;
	}
}
