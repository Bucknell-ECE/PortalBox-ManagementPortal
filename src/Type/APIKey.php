<?php

namespace Portalbox\Type;

use InvalidArgumentException;
use Portalbox\Enumeration\Permission;

/**
 * APIKey represents a token that can be used to authenticate to the REST API
 * without establishing a User Session
 */
class APIKey {
	use \Portalbox\Trait\HasIdProperty;

	public const ERROR_INVALID_PERMISSION = 'permission must be one of the enumerated permissions';

	/** The name of this API key */
	protected string $name = '';

	/**
	 * The token that can be presented to authenticate to the API in the
	 * absence of a User Session
	 */
	protected ?string $token = null;

	/**
	 * A list of permissions assigned to this API key
	 *
	 * @var Permission[]
	 */
	protected array $permissions = [];

	/**
	 * Get the name of this API key
	 */
	public function name(): string {
		return $this->name;
	}

	/**
	 * Set the name of this API key
	 *
	 * @throws InvalidArgumentException if the name is the empty string
	 */
	public function set_name(string $name): self {
		if ($name === '') {
			throw new InvalidArgumentException('You must specify the API key\'s name');
		}

		$this->name = $name;
		return $this;
	}

	/**
	 * Get token that can be presented to authenticate to the API in the
	 * absence of a Session
	 */
	public function token(): string {
		if (null === $this->token) {
			$this->token = $this->create_token();
		}
		return $this->token;
	}

	/**
	 * Set token that can be presented to authenticate to the API in the
	 * absence of a Session
	 */
	public function set_token(string $token): self {
		$this->token = $token;
		return $this;
	}

	private function create_token(): string {
		// If libsodium is available use it :)
		if (true === function_exists('random_bytes')) {
			return bin2hex(random_bytes(16));
		} else {
			return sprintf('%04X%04X%04X%04X%04X%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
		}
	}

	/**
	 * Get the permissions for this role
	 *
	 * @return int[]  the list of the role's permissions
	 */
	public function permissions(): array {
		return $this->permissions;
	}

	/**
	 * Set the permissions for this role
	 *
	 * @param int[] $permissions  the permissions for this role
	 * @throws InvalidArgumentException if any of the  specified permission are
	 *             not not one of the public constants from Permission
	 */
	public function set_permissions(array $permissions): self {
		foreach ($permissions as $permission) {
			if (!($permission instanceof Permission)) {
				throw new InvalidArgumentException(self::ERROR_INVALID_PERMISSION);
			}
		}

		$this->permissions = $permissions;
		return $this;
	}
}
