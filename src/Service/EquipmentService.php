<?php

declare(strict_types=1);

namespace Portalbox\Service;

use InvalidArgumentException;
use Portalbox\Enumeration\Permission;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Exception\NotFoundException;
use Portalbox\Model\EquipmentModel;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Model\LocationModel;
use Portalbox\Query\EquipmentQuery;
use Portalbox\Session;
use Portalbox\Type\Equipment;

/**
 * Manage Equipment
 */
class EquipmentService {
	public const ERROR_UNAUTHENTICATED_CREATE = 'You must be authenticated to create equipment';
	public const ERROR_UNAUTHORIZED_CREATE = 'You are not authorized to create equipment';
	public const ERROR_INVALID_EQUIPMENT_DATA = 'We can not create equipment from the provided data';
	public const ERROR_NAME_IS_REQUIRED = '\'name\' is a required field';
	public const ERROR_TYPE_ID_IS_REQUIRED = '\'type_id\' is a required field';
	public const ERROR_INVALID_TYPE_ID = '\'type_id\' must correspond to a valid equipment type';
	public const ERROR_LOCATION_ID_IS_REQUIRED = '\'location_id\' is a required field';
	public const ERROR_INVALID_LOCATION_ID = '\'location_id\' must correspond to a valid location';
	public const ERROR_MAC_ADDRESS_IS_REQUIRED = '\'mac_address\' is a required field';
	public const ERROR_TIMEOUT_IS_REQUIRED = '\'timeout\' is a required field';
	public const ERROR_IN_SERVICE_IS_REQUIRED = '\'in_service\' is a required field';
	public const ERROR_SERVICE_MINUTES_IS_INVALID = '\'service_minutes\' if provided must be a positive integer';

	public const ERROR_UNAUTHENTICATED_READ = 'You must be authenticated to read equipment';
	public const ERROR_UNAUTHORIZED_READ = 'You are not authorized to read the specified equipment';
	public const ERROR_EQUIPMENT_NOT_FOUND = 'We have no record of that equipment';

	public const ERROR_LOCATION_FILTER_MUST_BE_INT = 'The value of the location must be the integer id of the location';

	protected Session $session;
	protected EquipmentModel $equipmentModel;
	protected EquipmentTypeModel $equipmentTypeModel;
	protected LocationModel $locationModel;

	public function __construct(
		Session $session,
		EquipmentModel $equipmentModel,
		EquipmentTypeModel $equipmentTypeModel,
		LocationModel $locationModel
	) {
		$this->session = $session;
		$this->equipmentModel = $equipmentModel;
		$this->equipmentTypeModel = $equipmentTypeModel;
		$this->locationModel = $locationModel;
	}

	/**
	 * Create equipment from the specified data stream
	 *
	 * @param string $filePath  the path to a file from which to read json data
	 * @return Equipment  The equipment which was added
	 * @throws AuthenticationException  if no user is authenticated
	 * @throws AuthorizationException  if the authenticated user may not create
	 *      equipment
	 * @throws InvalidArgumentException  if the file can not be read or does not
	 *      contain JSON encoded data
	 */
	public function create(string $filePath): Equipment {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException(self::ERROR_UNAUTHENTICATED_CREATE);
		}

		if (!$authenticatedUser->role()->has_permission(Permission::CREATE_EQUIPMENT)) {
			throw new AuthorizationException(self::ERROR_UNAUTHORIZED_CREATE);
		}

		$data = file_get_contents($filePath);
		if ($data === false) {
			throw new InvalidArgumentException(self::ERROR_INVALID_EQUIPMENT_DATA);
		}

		$equipment = json_decode($data, TRUE);
		if (!is_array($equipment)) {
			throw new InvalidArgumentException(self::ERROR_INVALID_EQUIPMENT_DATA);
		}

		return $this->equipmentModel->create($this->deserialize($equipment));
	}

	/**
	 * Deserialize an Equipment object from a dictionary
	 *
	 * @param array data  a dictionary representing a Equipment
	 * @return Equipment  an object based on the data specified
	 * @throws InvalidArgumentException if a required field is not specified
	 */
	private function deserialize(array $data): Equipment {
		$name = strip_tags($data['name'] ?? '');
		if (empty($name)) {
			throw new InvalidArgumentException(self::ERROR_NAME_IS_REQUIRED);
		}

		$type_id = filter_var($data['type_id'] ?? '', FILTER_VALIDATE_INT);
		if ($type_id === false) {
			throw new InvalidArgumentException(self::ERROR_TYPE_ID_IS_REQUIRED);
		}
		$type = $this->equipmentTypeModel->read($type_id);
		if ($type === null) {
			throw new InvalidArgumentException(self::ERROR_INVALID_TYPE_ID);
		}

		$location_id = filter_var($data['location_id'] ?? '', FILTER_VALIDATE_INT);
		if ($location_id === false) {
			throw new InvalidArgumentException(self::ERROR_LOCATION_ID_IS_REQUIRED);
		}
		$location = $this->locationModel->read($location_id);
		if ($location === null) {
			throw new InvalidArgumentException(self::ERROR_INVALID_LOCATION_ID);
		}

		// we use a regex because FILTER_VALIDATE_MAC requires byte separators
		// which we don't want to require
		$mac_address = filter_var(
			$data['mac_address'] ?? '',
			FILTER_VALIDATE_REGEXP,
			['options' => ['regexp' => '/^([0-9A-Fa-f]{2}[:-]?){5}([0-9A-Fa-f]{2})$/']]
		);
		if ($mac_address === false) {
			throw new InvalidArgumentException(self::ERROR_MAC_ADDRESS_IS_REQUIRED);
		}

		$timeout = filter_var(
			$data['timeout'] ?? '',
			FILTER_VALIDATE_INT,
			['options' => ['min_range' => 0]]
		);
		if ($timeout === false) {
			throw new InvalidArgumentException(self::ERROR_TIMEOUT_IS_REQUIRED);
		}

		$in_service = filter_var(
			$data['in_service'] ?? 'fail',
			FILTER_VALIDATE_BOOLEAN,
			FILTER_NULL_ON_FAILURE
		);
		if ($in_service === NULL) {
			throw new InvalidArgumentException(self::ERROR_IN_SERVICE_IS_REQUIRED);
		}

		// service minutes is optional and should default to 0
		$service_minutes = 0;
		if (array_key_exists('service_minutes', $data)) {
			$service_minutes = filter_var(
				$data['service_minutes'],
				FILTER_VALIDATE_INT,
				['options' => ['min_range' => 0]]
			);
			if ($service_minutes === false) {
				throw new InvalidArgumentException(self::ERROR_SERVICE_MINUTES_IS_INVALID);
			}
		}

		return (new Equipment())
			->set_name($name)
			->set_type($type)
			->set_location($location)
			->set_mac_address($mac_address)
			->set_timeout($timeout)
			->set_is_in_service($in_service)
			->set_service_minutes($service_minutes);
	}

	/**
	 * Read equipment by id
	 *
	 * @param int $id  the unique id of the equipment to read
	 * @return Equipment  the equipment
	 * @throws AuthenticationException  if no user is authenticated
	 * @throws AuthorizationException  if the authenticated user may not read
	 *      the equipment
	 * @throws NotFoundException  if the equipment is not found
	 */
	public function read(int $id): Equipment {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException(self::ERROR_UNAUTHENTICATED_READ);
		}

		$role = $authenticatedUser->role();
		if (!$role->has_permission(Permission::READ_EQUIPMENT)) {
			throw new AuthorizationException(self::ERROR_UNAUTHORIZED_READ);
		}

		$equipment = $this->equipmentModel->read($id);
		if ($equipment === null) {
			throw new NotFoundException(self::ERROR_EQUIPMENT_NOT_FOUND);
		}

		return $equipment;
	}

	/**
	 * Read all equipment matching the filters
	 *
	 * @param array<string, string>  filters that all equipment in the result set
	 *      must match
	 * @return Equipment[]  the equipment
	 * @throws InvalidArgumentException  if the filter specifies an equipment
	 *      type id or user id that is not an integer
	 */
	public function readAll(array $filters): array {
		$query = new EquipmentQuery();

		if (isset($filters['location_id']) && !empty($filters['location_id'])) {
			$id = filter_var($filters['location_id'], FILTER_VALIDATE_INT);
			if ($id === false) {
				throw new InvalidArgumentException(self::ERROR_LOCATION_FILTER_MUST_BE_INT);
			}

			$query->set_location_id($id);
		}

		// by default do not include out of service equipment
		if (
			!isset($filters['include_out_of_service'])
			|| empty($filters['include_out_of_service'])
		) {
			$query->set_exclude_out_of_service(true);
		}

		return $this->equipmentModel->search($query);
	}
}
