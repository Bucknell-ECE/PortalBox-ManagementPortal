<?php

declare(strict_types=1);

namespace Portalbox\Service;

use InvalidArgumentException;
use Portalbox\Entity\BadgeRule;
use Portalbox\Entity\Permission;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Exception\NotFoundException;
use Portalbox\Model\BadgeRuleModel;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Session\SessionInterface;

/**
 * Manage badge rules
 */
class BadgeRuleService {
	public const ERROR_UNAUTHENTICATED_CREATE = 'You must be authenticated to create badge rules';
	public const ERROR_UNAUTHORIZED_CREATE = 'You are not authorized to create badge rules';
	public const ERROR_INVALID_BADGE_RULE_DATA = 'We can not construct a badge rule from the provided data';
	public const ERROR_NAME_IS_REQUIRED = '\'name\' is a required field';
	public const ERROR_NAME_IS_INVALID = '\'name\' must not be an empty string';
	public const ERROR_EQUIPMENT_TYPES_ARE_INVALID = '\'equipment_types\' must be an array of the ids of equipment types';

	public const ERROR_UNAUTHENTICATED_READ = 'You must be authenticated to read badge rules';
	public const ERROR_UNAUTHORIZED_READ = 'You are not authorized to read the specified badge rule(s)';
	public const ERROR_BADGE_RULE_NOT_FOUND = 'We have no record of that badge rule';

	public const ERROR_UNAUTHENTICATED_MODIFY = 'You must be authenticated to modify badge rules';
	public const ERROR_UNAUTHORIZED_MODIFY = 'You are not authorized to modify badge rules';

	public const ERROR_UNAUTHENTICATED_DELETE = 'You must be authenticated to delete badge rules';
	public const ERROR_UNAUTHORIZED_DELETE = 'You are not authorized to delete badge rules';

	protected SessionInterface $session;
	protected BadgeRuleModel $badgeRuleModel;
	protected EquipmentTypeModel $equipmentTypeModel;

	public function __construct(
		SessionInterface $session,
		BadgeRuleModel $badgeRuleModel,
		EquipmentTypeModel $equipmentTypeModel
	) {
		$this->session = $session;
		$this->badgeRuleModel = $badgeRuleModel;
		$this->equipmentTypeModel = $equipmentTypeModel;
	}

	/**
	 * Deserialize a BadgeRule object from a dictionary
	 *
	 * @param array data - a dictionary representing a BadgeRule
	 * @return BadgeRule - a valid BadgeRule instance based on the data specified
	 * @throws InvalidArgumentException if a required field is not specified or
	 *      an unacceptable value is specified
	 */
	private function deserialize(array $data): BadgeRule {
		if (!array_key_exists('name', $data)) {
			throw new InvalidArgumentException(self::ERROR_NAME_IS_REQUIRED);
		}

		$name = strip_tags($data['name']);
		if (empty($name)) {
			throw new InvalidArgumentException(self::ERROR_NAME_IS_INVALID);
		}

		$equipment_type_ids = [];

		// equipment types are optional
		if (array_key_exists('equipment_types', $data)) {
			if (!is_array($data['equipment_types'])) {
				throw new InvalidArgumentException(self::ERROR_EQUIPMENT_TYPES_ARE_INVALID);
			}

			$known_equipment_type_ids = [];
			foreach ($this->equipmentTypeModel->search() as $equipment_type) {
				$known_equipment_type_ids[] = $equipment_type->id();
			}

			foreach ($data['equipment_types'] as $id) {
				$equipment_type_id = filter_var($id, FILTER_VALIDATE_INT);
				if($equipment_type_id === false) {
					throw new InvalidArgumentException(self::ERROR_EQUIPMENT_TYPES_ARE_INVALID);
				}

				if (!in_array($id, $known_equipment_type_ids)) {
					throw new InvalidArgumentException(self::ERROR_EQUIPMENT_TYPES_ARE_INVALID);
				}

				$equipment_type_ids[] = $id;
			}
		}

		return (new BadgeRule())
			->set_name($name)
			->set_equipment_type_ids($equipment_type_ids);
	}

	/**
	 * Create a badge from the specified data stream
	 *
	 * @param string $filePath - the path to a file from which to read json data
	 * @return BadgeRule - the badge rule which was added
	 * @throws AuthenticationException  if no user is authenticated
	 * @throws AuthorizationException  if the authenticated user may not create
	 *      badge rules
	 * @throws InvalidArgumentException  if the file can not be read or does not
	 *      contain JSON encoded data
	 */
	public function create(string $filePath): BadgeRule {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException(self::ERROR_UNAUTHENTICATED_CREATE);
		}

		if (!$authenticatedUser->role()->has_permission(Permission::CREATE_BADGE_RULE)) {
			throw new AuthorizationException(self::ERROR_UNAUTHORIZED_CREATE);
		}

		$data = file_get_contents($filePath);
		if ($data === false) {
			throw new InvalidArgumentException(self::ERROR_INVALID_BADGE_RULE_DATA);
		}

		$rule = json_decode($data, TRUE);
		if (!is_array($rule)) {
			throw new InvalidArgumentException(self::ERROR_INVALID_BADGE_RULE_DATA);
		}

		return $this->badgeRuleModel->create($this->deserialize($rule));
	}

	/**
	 * Read a badge rule by id
	 *
	 * @param int $id  the unique id of the badge rule to read
	 * @return BadgeRule  the badge rule
	 * @throws AuthenticationException  if no user is authenticated
	 * @throws AuthorizationException  if the authenticated user may not read
	 *      the badge rule
	 * @throws NotFoundException  if the badge rule is not found
	 */
	public function read(int $id): BadgeRule {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException(self::ERROR_UNAUTHENTICATED_READ);
		}

		$role = $authenticatedUser->role();
		if (!$role->has_permission(Permission::READ_BADGE_RULE)) {
			throw new AuthorizationException(self::ERROR_UNAUTHORIZED_READ);
		}

		$rule = $this->badgeRuleModel->read($id);
		if ($rule === null) {
			throw new NotFoundException(self::ERROR_BADGE_RULE_NOT_FOUND);
		}

		return $rule;
	}

	/**
	 * Read all badge rules
	 *
	 * @return BadgeRule[]  the badge rules
	 * @throws AuthenticationException  if no user is authenticated
	 * @throws AuthorizationException  if the authenticated user may not read
	 *      the badge rules
	 */
	public function readAll(): array {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException(self::ERROR_UNAUTHENTICATED_READ);
		}

		$role = $authenticatedUser->role();
		if (!$role->has_permission(Permission::LIST_BADGE_RULES)) {
			throw new AuthorizationException(self::ERROR_UNAUTHORIZED_READ);
		}

		return $this->badgeRuleModel->search();
	}

	/**
	 * Modify a badge rule using data read from the specified data stream
	 *
	 * @param int $id  the unique id of the badge rule to modify
	 * @param string $filePath  the path to a file from which to read json data
	 * @return BadgeRule  the badge rule as modified
	 * @throws AuthenticationException  if no user is authenticated
	 * @throws AuthorizationException  if the authenticated user may not update
	 *      badge rules
	 * @throws InvalidArgumentException  if the file can not be read or does not
	 *      contain JSON encoded data
	 * @throws NotFoundException  if the badge rule is not found
	 */
	public function update(int $id, string $filePath): BadgeRule {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException(self::ERROR_UNAUTHENTICATED_MODIFY);
		}

		if (!$authenticatedUser->role()->has_permission(Permission::MODIFY_BADGE_RULE)) {
			throw new AuthorizationException(self::ERROR_UNAUTHORIZED_MODIFY);
		}

		$data = file_get_contents($filePath);
		if ($data === false) {
			throw new InvalidArgumentException(self::ERROR_INVALID_BADGE_RULE_DATA);
		}

		$rule = json_decode($data, TRUE);
		if (!is_array($rule)) {
			throw new InvalidArgumentException(self::ERROR_INVALID_BADGE_RULE_DATA);
		}

		$rule = $this->badgeRuleModel->update(
			$this->deserialize($rule)->set_id($id)
		);
		if ($rule === null) {
			throw new NotFoundException(self::ERROR_BADGE_RULE_NOT_FOUND);
		}

		return $rule;
	}

	/**
	 * Delete a badge rule
	 *
	 * @param int $id  the unique id of the badge rule to delete
	 * @return BadgeRule  the badge rule which was deleted
	 * @throws AuthenticationException  if no user is authenticated
	 * @throws AuthorizationException  if the authenticated user may not delete
	 *      badge rules
	 * @throws NotFoundException  if the badge rule is not found
	 */
	public function delete(int $id): BadgeRule {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException(self::ERROR_UNAUTHENTICATED_DELETE);
		}

		if (!$authenticatedUser->role()->has_permission(Permission::DELETE_BADGE_RULE)) {
			throw new AuthorizationException(self::ERROR_UNAUTHORIZED_DELETE);
		}

		$rule = $this->badgeRuleModel->delete($id);

		if ($rule === null) {
			throw new NotFoundException(self::ERROR_BADGE_RULE_NOT_FOUND);
		}

		return $rule;
	}
}
