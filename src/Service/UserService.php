<?php

namespace Portalbox\Service;

use InvalidArgumentException;
use Portalbox\Entity\Permission;
use Portalbox\Entity\User;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Exception\NotFoundException;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Model\RoleModel;
use Portalbox\Model\UserModel;
use Portalbox\Session\SessionInterface;

/**
 * Manage Users
 *
 * @todo bring authorization checks into service methods
 */
class UserService {
	public const ERROR_INVALID_CSV_RECORD_LENGTH = 'Import files must contain 3 columns: "Name", "Email Address", and "Role Id"';
	public const ERROR_INVALID_CSV_ROLE = '"Role" must be the name of an existing role';
	public const ERROR_INVALID_EMAIL = 'Email must be a valid email address';
	public const ERROR_NOT_AUTHORIZED_TO_PATCH_AUTHORIZATIONS = 'You are not authorized to change a user\'s authorizations';
	public const ERROR_INVALID_AUTHORIZATIONS = '"authorizations" must be a list of equipment type ids';
	public const ERROR_NOT_AUTHORIZED_TO_PATCH_PIN = 'Users may only change their own PIN';
	public const ERROR_INVALID_PIN = 'A user\'s PIN must be a string of four digits 0-9';
	public const ERROR_INVALID_PATCH = 'User properties must be serialized as a json encoded object';
	public const ERROR_USER_NOT_FOUND = 'We have no record of that user';

	protected EquipmentTypeModel $equipmentTypeModel;
	protected RoleModel $roleModel;
	protected UserModel $userModel;

	public function __construct(
		SessionInterface $session,
		EquipmentTypeModel $equipmentTypeModel,
		RoleModel $roleModel,
		UserModel $userModel
	) {
		$this->session = $session;
		$this->equipmentTypeModel = $equipmentTypeModel;
		$this->roleModel = $roleModel;
		$this->userModel = $userModel;
	}

	/**
	 * Import users from the open file handle
	 *
	 * We expect the first line to be a header and that the input is three
	 * columns: Name, Email Address, and Role Id in that order.
	 *
	 * @todo be smarter about column headers and column order. Maybe allow
	 *     optional columns.
	 *
	 * @param string $filePath  the path to a file from which to read csv data
	 * @return User[]  The list of users which were added
	 */
	public function import(string $filePath): array {
		// we don't expect many roles in the system so let's just cache them
		$roles = [];
		foreach ($this->roleModel->search() as $role) {
			$roles[$role->name()] = $role;
		}

		// read and discard header line
		$fileHandle = fopen($filePath, 'r');
		$header = fgetcsv($fileHandle);

		// read lines, validating each, to accumulate users
		$records = [];
		while ($user = fgetcsv($fileHandle)) {
			if (count($user) !== 3) {
				throw new InvalidArgumentException(self::ERROR_INVALID_CSV_RECORD_LENGTH);
			}

			$role = trim($user[2]);
			if (!array_key_exists($role, $roles)) {
				throw new InvalidArgumentException(self::ERROR_INVALID_CSV_ROLE);
			}

			$email = filter_var(trim($user[1]), FILTER_VALIDATE_EMAIL);
			if ($email === false) {
				throw new InvalidArgumentException(self::ERROR_INVALID_EMAIL);
			}

			$records[] = [
				'name' => strip_tags(trim($user[0])),
				'email' => $email,
				'role' => $roles[$role]
			];
		}

		fclose($fileHandle);

		// persist users to database
		// @todo wrap in transaction
		$users = [];
		foreach ($records as $record) {
			$users[] = $this->userModel->create(
				(new User())
					->set_name($record['name'])
					->set_email($record['email'])
					->set_is_active(true)
					->set_role($record['role'])
			);
		}

		return $users;
	}

	/**
	 * Persist changes to a user's properties
	 *
	 * @param int $userId  the unique id of the user to modify
	 * @param string $filePath  the path to a file from which to read a JSON
	 *      encoded patch
	 * @return User  the user as modified
	 * @throws InvalidArgumentException  if filePath is not the path to a
	 *      readable file, if the file does not contain a JSON encoded "object"
	 *      i.e. a key/value list, or the keys of the list are not User
	 *      properties supported for patching
	 * @throws NotFoundException  if the user is not found
	 */
	public function patch(int $userId, string $filePath): User {
		if ($this->session->get_authenticated_user() === null) {
			throw new AuthenticationException();
		}

		$user = $this->userModel->read($userId);
		if ($user === null) {
			throw new NotFoundException(self::ERROR_USER_NOT_FOUND);
		}

		$data = file_get_contents($filePath);
		if ($data === false) {
			throw new InvalidArgumentException(self::ERROR_INVALID_PATCH);
		}

		$patch = json_decode($data, TRUE);
		if (!is_array($patch)) {
			throw new InvalidArgumentException(self::ERROR_INVALID_PATCH);
		}

		foreach ($patch as $property => $value) {
			// @todo this would be a good place to use `match` when we officially
			// drop PHP 7.4
			switch ($property) {
				case 'authorizations':
					$user = $this->patchUserAuthorizations($user, $value);
					break;
				case 'pin':
					$user = $this->patchUserPIN($user, $value);
					break;
				default:
					throw new InvalidArgumentException(self::ERROR_INVALID_PATCH);
			}
		}

		return $this->userModel->update($user);
	}

	/**
	 * Apply a patch to the in memory user's authorizations
	 *
	 * Note This method does not persist the user to the database
	 *
	 * @param User $user  the user to be patched
	 * @param mixed $equipment_types  the equipment_type the user is to be
	 *      authorized for
	 * @return User the user with the proposed authorizations applied
	 * @throws InvalidArgumentException if equipment_types is not a list of
	 *      integers corresponding to equipment types
	 * @throws AuthorizationException  if the user does not have the
	 *      CREATE_EQUIPMENT_AUTHORIZATION, DELETE_EQUIPMENT_AUTHORIZATION, and
	 *      MODIFY_USER permissions
	 * @todo allow users to have CREATE_EQUIPMENT_AUTHORIZATION and
	 *      DELETE_EQUIPMENT_AUTHORIZATION permissions separately and restrict
	 *      changes to adding and removing accordingly
	 */
	private function patchUserAuthorizations(User $user, mixed $equipment_types): User {
		$role = $this->session->get_authenticated_user()->role();
		if(!(
			$role->has_permission(Permission::CREATE_EQUIPMENT_AUTHORIZATION)
			&& $role->has_permission(Permission::DELETE_EQUIPMENT_AUTHORIZATION)
			&& $role->has_permission(Permission::MODIFY_USER)
		)) {
			throw new AuthorizationException(self::ERROR_NOT_AUTHORIZED_TO_PATCH_AUTHORIZATIONS);
		}

		if (!is_array($equipment_types)) {
			throw new InvalidArgumentException(self::ERROR_INVALID_AUTHORIZATIONS);
		}

		$authorizations = [];
		foreach ($equipment_types as $equipment_type_id) {
			$id = filter_var($equipment_type_id, FILTER_VALIDATE_INT);
			if ($id === false) {
				throw new InvalidArgumentException(self::ERROR_INVALID_AUTHORIZATIONS);
			}

			$equipment_type = $this->equipmentTypeModel->read($equipment_type_id);
			if ($equipment_type === null) {
				throw new InvalidArgumentException(self::ERROR_INVALID_AUTHORIZATIONS);
			}

			if (!in_array($id, $authorizations)) {
				$authorizations[] = $equipment_type_id;
			}
		}

		return $user->set_authorizations($authorizations);
	}

	/**
	 * Apply a patch to the in memory user's pin
	 *
	 * Note This method does not persist the user to the database
	 *
	 * @param User $user  the user to be patched
	 * @param mixed $value  the pin for the user
	 * @return User the user with the proposed pin set
	 * @throws AuthorizationException  if the user to be patched is not the
	 *      authenticated user
	 */
	private function patchUserPIN(User $user, mixed $value): User {
		if ($this->session->get_authenticated_user()->id() !== $user->id()) {
			throw new AuthorizationException(self::ERROR_NOT_AUTHORIZED_TO_PATCH_PIN);
		}

		if (!is_string($value)) {
			throw new InvalidArgumentException(self::ERROR_INVALID_PIN);
		}

		$pin = trim($value);
		if (preg_match('/^\d{4}$/', $pin) !== 1) {
			throw new InvalidArgumentException(self::ERROR_INVALID_PIN);
		}

		return $user->set_pin(password_hash($pin, PASSWORD_DEFAULT));
	}
}
