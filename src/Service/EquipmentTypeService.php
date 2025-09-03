<?php

declare(strict_types=1);

namespace Portalbox\Service;

use InvalidArgumentException;
use Portalbox\Entity\ChargePolicy;
use Portalbox\Entity\EquipmentType;
use Portalbox\Entity\Permission;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Exception\NotFoundException;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Session\SessionInterface;

/**
 * Manage Equipment Types
 */
class EquipmentTypeService {
	public const ERROR_UNAUTHENTICATED_CREATE = 'You must be authenticated to create equipment types';
	public const ERROR_UNAUTHORIZED_CREATE = 'You are not authorized to create equipment types';
	public const ERROR_INVALID_EQUIPMENT_TYPE_DATA = 'We can not create an equipment type from the provided data';
	public const ERROR_NAME_IS_REQUIRED = '\'name\' is a required field';
	public const ERROR_REQUIRES_TRAINING_IS_REQUIRED = '\'requires_training\' is a required field';
	public const ERROR_INVALID_CHARGE_POLICY = '\'charge_policy_id\' is required and must be a valid charge policy id';
	public const ERROR_INVALID_RATE = '\'charge_rate\' is a required and must be a positive number';
	public const ERROR_ALLOWS_PROXY_IS_REQUIRED = '\'allow_proxy\' is a required field';

	public const ERROR_UNAUTHENTICATED_READ = 'You must be authenticated to read equipment types';
	public const ERROR_UNAUTHORIZED_READ = 'You are not authorized to read the specified equipment type(s)';

	public const ERROR_UNAUTHENTICATED_MODIFY = 'You must be authenticated to modify equipment types';
	public const ERROR_UNAUTHORIZED_MODIFY = 'You are not authorized to modify equipment types';
	public const ERROR_EQUIPMENT_TYPE_NOT_FOUND = 'We have no record of that equipment type';


	protected SessionInterface $session;
	protected EquipmentTypeModel $equipmentTypeModel;

	public function __construct(
		SessionInterface $session,
		EquipmentTypeModel $equipmentTypeModel
	) {
		$this->session = $session;
		$this->equipmentTypeModel = $equipmentTypeModel;
	}

	/**
	 * Create an equipment type from the specified data stream
	 *
	 * @param string $filePath  the path to a file from which to read json data
	 * @return EquipmentType  The equipment type which was added
	 * @throws AuthenticationException  if no user is authenticated
	 * @throws AuthorizationException  if the authenticated user may not create
	 *      equipment types
	 * @throws InvalidArgumentException  if the file can not be read or does not
	 *      contain JSON encoded data
	 */
	public function create(string $filePath): EquipmentType {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException(self::ERROR_UNAUTHENTICATED_CREATE);
		}

		if (!$authenticatedUser->role()->has_permission(Permission::CREATE_EQUIPMENT_TYPE)) {
			throw new AuthorizationException(self::ERROR_UNAUTHORIZED_CREATE);
		}

		$data = file_get_contents($filePath);
		if ($data === false) {
			throw new InvalidArgumentException(self::ERROR_INVALID_EQUIPMENT_TYPE_DATA);
		}

		$equipmentType = json_decode($data, TRUE);
		if (!is_array($equipmentType)) {
			throw new InvalidArgumentException(self::ERROR_INVALID_EQUIPMENT_TYPE_DATA);
		}

		return $this->equipmentTypeModel->create($this->deserialize($equipmentType));
	}

	/**
	 * Read an equipment type by id
	 *
	 * @param int $id  the unique id of the equipment type to read
	 * @return EquipmentType  the equipment type
	 * @throws AuthenticationException  if no user is authenticated
	 * @throws AuthorizationException  if the authenticated user may not read
	 *      all equipment types
	 * @throws NotFoundException  if the equipment type is not found
	 */
	public function read(int $id): EquipmentType {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException(self::ERROR_UNAUTHENTICATED_READ);
		}

		if (!$authenticatedUser->role()->has_permission(Permission::READ_EQUIPMENT_TYPE)) {
			throw new AuthorizationException(self::ERROR_UNAUTHORIZED_READ);
		}

		$equipmentType = $this->equipmentTypeModel->read($id);
		if ($equipmentType === null) {
			throw new NotFoundException(self::ERROR_EQUIPMENT_TYPE_NOT_FOUND);
		}

		return $equipmentType;
	}

	/**
	 * Read all equipment types
	 *
	 * @return EquipmentType[]  the equipment types
	 * @throws AuthenticationException  if no user is authenticated
	 * @throws AuthorizationException  if the authenticated user may not read
	 *      all equipment types
	 */
	public function readAll(): array {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException(self::ERROR_UNAUTHENTICATED_READ);
		}

		if (!$authenticatedUser->role()->has_permission(Permission::LIST_EQUIPMENT_TYPES)) {
			throw new AuthorizationException(self::ERROR_UNAUTHORIZED_READ);
		}

		return $this->equipmentTypeModel->search();
	}

	/**
	 * Update an equipment type
	 *
	 * @param int $id  the id of the equipment type to update
	 * @param string $filePath  the path to a file from which to read json data
	 * @return EquipmentType  The equipment type which was modified
	 * @throws AuthenticationException  if no user is authenticated
	 * @throws AuthorizationException  if the authenticated user may not modify
	 *      equipment types
	 * @throws InvalidArgumentException  if the file can not be read or does not
	 *      contain JSON encoded data
	 */
	public function update(int $id, string $filePath): EquipmentType {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException(self::ERROR_UNAUTHENTICATED_MODIFY);
		}

		if (!$authenticatedUser->role()->has_permission(Permission::MODIFY_EQUIPMENT_TYPE)) {
			throw new AuthorizationException(self::ERROR_UNAUTHORIZED_MODIFY);
		}

		$equipmentType = $this->equipmentTypeModel->read($id);
		if ($equipmentType === null) {
			throw new NotFoundException(self::ERROR_EQUIPMENT_TYPE_NOT_FOUND);
		}

		$data = file_get_contents($filePath);
		if ($data === false) {
			throw new InvalidArgumentException(self::ERROR_INVALID_EQUIPMENT_TYPE_DATA);
		}

		$equipmentType = json_decode($data, TRUE);
		if (!is_array($equipmentType)) {
			throw new InvalidArgumentException(self::ERROR_INVALID_EQUIPMENT_TYPE_DATA);
		}

		return $this->equipmentTypeModel->update(
			$this->deserialize($equipmentType)
				->set_id($id)
		);
	}

	/**
	 * Deserialize an EquipmentType entity object from a dictionary
	 *
	 * @param array data - a dictionary representing an equipment type
	 * @return EquipmentType - a valid entity object based on the data specified
	 * @throws InvalidArgumentException if a required field is not specified
	 */
	private function deserialize(array $data): EquipmentType {
		$name = strip_tags($data['name'] ?? '');
		if (empty($name)) {
			throw new InvalidArgumentException(self::ERROR_NAME_IS_REQUIRED);
		}

		$requiresTraining = filter_var($data['requires_training'] ?? true, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
		if ($requiresTraining === null) {
			throw new InvalidArgumentException(self::ERROR_REQUIRES_TRAINING_IS_REQUIRED);
		}

		$chargePolicyId = filter_var($data['charge_policy_id'] ?? '', FILTER_VALIDATE_INT);
		if ($chargePolicyId === false || !ChargePolicy::is_valid($chargePolicyId)) {
			throw new InvalidArgumentException(self::ERROR_INVALID_CHARGE_POLICY);
		}

		$chargeRate = filter_var(
			$data['charge_rate'] ?? '',
			FILTER_VALIDATE_FLOAT,
			['min_range' => 0.0]
		);
		if ($chargeRate === false) {
			throw new InvalidArgumentException(self::ERROR_INVALID_RATE);
		}

		$allowProxy = filter_var($data['allow_proxy'] ?? false, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
		if ($allowProxy === null) {
			throw new InvalidArgumentException(self::ERROR_ALLOWS_PROXY_IS_REQUIRED);
		}

		return (new EquipmentType())
			->set_name($name)
			->set_requires_training($requiresTraining)
			->set_charge_rate((string)$chargeRate)
			->set_charge_policy_id($chargePolicyId)
			->set_allow_proxy($allowProxy);
	}
}
