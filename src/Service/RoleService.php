<?php

declare(strict_types=1);

namespace Portalbox\Service;

use InvalidArgumentException;
use Portalbox\Enumeration\Permission;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Exception\NotFoundException;
use Portalbox\Model\RoleModel;
use Portalbox\Session;
use Portalbox\Type\Role;

/**
 * Manage Roles
 */
class RoleService {
	public const ERROR_UNAUTHENTICATED_CREATE = 'You must be authenticated to create roles';
	public const ERROR_UNAUTHORIZED_CREATE = 'You are not authorized to create roles';
	public const ERROR_INVALID_ROLE_DATA = 'We can not construct a role from the provided data';
	public const ERROR_NAME_IS_REQUIRED = '\'name\' is a required field';
	public const ERROR_NAME_IS_INVALID = '\'name\' must not be an empty string';
	public const ERROR_DESCRIPTION_IS_REQUIRED = '\'description\' is a required field';
	public const ERROR_PERMISSIONS_ARE_REQUIRED = '\'permissions\' is a required field';
	public const ERROR_PERMISSIONS_ARE_INVALID = '\'permissions\' must be an array';

	public const ERROR_UNAUTHENTICATED_READ = 'You must be authenticated to read roles';
	public const ERROR_UNAUTHORIZED_READ = 'You are not authorized to read the specified role(s)';
	public const ERROR_ROLE_NOT_FOUND = 'We have no record of that role';

	public const ERROR_UNAUTHENTICATED_MODIFY = 'You must be authenticated to modify roles';
	public const ERROR_UNAUTHORIZED_MODIFY = 'You are not authorized to modify roles';

	protected Session $session;
	protected RoleModel $roleModel;

	public function __construct(
		Session $session,
		RoleModel $roleModel
	) {
		$this->session = $session;
		$this->roleModel = $roleModel;
	}

	/**
	 * Deserialize a Role object from a dictionary
	 *
	 * @param array data  a dictionary representing a Role
	 * @return Role  an object based on the data specified
	 * @throws InvalidArgumentException if a required field is not specified or
	 *      a value is unacceptable
	 */
	private function deserialize(array $data): Role {
		if (!array_key_exists('name', $data)) {
			throw new InvalidArgumentException(self::ERROR_NAME_IS_REQUIRED);
		}
		
		$name = strip_tags($data['name']);
		if (empty($name)) {
			throw new InvalidArgumentException(self::ERROR_NAME_IS_INVALID);
		}

		if (!array_key_exists('description', $data)) {
			throw new InvalidArgumentException(self::ERROR_DESCRIPTION_IS_REQUIRED);
		}

		if (!array_key_exists('permissions', $data)) {
			throw new InvalidArgumentException(self::ERROR_PERMISSIONS_ARE_REQUIRED);
		}

		$permissions = $data['permissions'];
		if (!is_array($permissions)) {
			throw new InvalidArgumentException(self::ERROR_PERMISSIONS_ARE_INVALID);
		}

		$validatedPermissions = [];
		foreach ($permissions as $permission) {
			$id = filter_var($permission, FILTER_VALIDATE_INT);
			if ($id === false) {
				throw new InvalidArgumentException(self::ERROR_PERMISSIONS_ARE_INVALID);
			}

			$permission = Permission::tryFrom($id);
			if ($permission === null) {
				throw new InvalidArgumentException(self::ERROR_PERMISSIONS_ARE_INVALID);
			}

			$validatedPermissions[] = $permission;
		}

		return (new Role())
			->set_name(strip_tags($name))
			->set_description(strip_tags($data['description']))
			->set_is_system_role(false)
			->set_permissions($validatedPermissions);
	}

	/**
	 * Create a role from the specified data stream
	 *
	 * @param string $filePath  the path to a file from which to read json data
	 * @return Role  the role which was added
	 * @throws AuthenticationException  if no user is authenticated
	 * @throws AuthorizationException  if the authenticated user may not create
	 *      roles
	 * @throws InvalidArgumentException  if the file can not be read or does not
	 *      contain JSON encoded data
	 */
	public function create(string $filePath): Role {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException(self::ERROR_UNAUTHENTICATED_CREATE);
		}

		if (!$authenticatedUser->role()->has_permission(Permission::CREATE_ROLE)) {
			throw new AuthorizationException(self::ERROR_UNAUTHORIZED_CREATE);
		}

		$data = file_get_contents($filePath);
		if ($data === false) {
			throw new InvalidArgumentException(self::ERROR_INVALID_ROLE_DATA);
		}

		$role = json_decode($data, TRUE);
		if (!is_array($role)) {
			throw new InvalidArgumentException(self::ERROR_INVALID_ROLE_DATA);
		}

		return $this->roleModel->create($this->deserialize($role));
	}

	/**
	 * Read a role by id
	 *
	 * @param int $id  the unique id of the role to read
	 * @return Role  the role
	 * @throws AuthenticationException  if no user is authenticated
	 * @throws AuthorizationException  if the authenticated user may not read
	 *      the role
	 * @throws NotFoundException  if the role is not found
	 */
	public function read(int $id): Role {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException(self::ERROR_UNAUTHENTICATED_READ);
		}

		if (!$authenticatedUser->role()->has_permission(Permission::READ_ROLE)) {
			throw new AuthorizationException(self::ERROR_UNAUTHORIZED_READ);
		}

		$role = $this->roleModel->read($id);
		if ($role === null) {
			throw new NotFoundException(self::ERROR_ROLE_NOT_FOUND);
		}

		return $role;
	}

	/**
	 * Read all roles
	 *
	 * @return Role[]  the roles
	 * @throws AuthenticationException  if no user is authenticated
	 * @throws AuthorizationException  if the authenticated user may not read
	 *      the roles
	 */
	public function readAll(): array {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException(self::ERROR_UNAUTHENTICATED_READ);
		}

		if (!$authenticatedUser->role()->has_permission(Permission::LIST_ROLES)) {
			throw new AuthorizationException(self::ERROR_UNAUTHORIZED_READ);
		}

		return $this->roleModel->search();
	}

	/**
	 * Modify a role using data read from the specified data stream
	 *
	 * @param int $id  the unique id of the role to modify
	 * @param string $filePath  the path to a file from which to read json data
	 * @return Role  the role as modified
	 * @throws AuthenticationException  if no user is authenticated
	 * @throws AuthorizationException  if the authenticated user may not update
	 *      roles
	 * @throws InvalidArgumentException  if the file can not be read or does not
	 *      contain JSON encoded data
	 * @throws NotFoundException  if the role is not found
	 */
	public function update(int $id, string $filePath): Role {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException(self::ERROR_UNAUTHENTICATED_MODIFY);
		}

		if (!$authenticatedUser->role()->has_permission(Permission::MODIFY_ROLE)) {
			throw new AuthorizationException(self::ERROR_UNAUTHORIZED_MODIFY);
		}

		$data = file_get_contents($filePath);
		if ($data === false) {
			throw new InvalidArgumentException(self::ERROR_INVALID_ROLE_DATA);
		}

		$role = json_decode($data, TRUE);
		if (!is_array($role)) {
			throw new InvalidArgumentException(self::ERROR_INVALID_ROLE_DATA);
		}

		$role = $this->roleModel->update(
			$this->deserialize($role)->set_id($id)
		);
		if ($role === null) {
			throw new NotFoundException(self::ERROR_ROLE_NOT_FOUND);
		}

		return $role;
	}
}
