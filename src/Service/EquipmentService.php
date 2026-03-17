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
use Portalbox\Query\EquipmentQuery;
use Portalbox\Session;
use Portalbox\Type\Equipment;

/**
 * Manage Equipment
 */
class EquipmentService {
	public const ERROR_UNAUTHENTICATED_READ = 'You must be authenticated to read equipment';
	public const ERROR_UNAUTHORIZED_READ = 'You are not authorized to read the specified equipment';
	public const ERROR_EQUIPMENT_NOT_FOUND = 'We have no record of that equipment';

	public const ERROR_LOCATION_FILTER_MUST_BE_INT = 'The value of the location must be the integer id of the location';

	protected Session $session;
	protected EquipmentModel $equipmentModel;
	protected EquipmentTypeModel $equipmentTypeModel;

	public function __construct(
		Session $session,
		EquipmentModel $equipmentModel,
		EquipmentTypeModel $equipmentTypeModel
	) {
		$this->session = $session;
		$this->equipmentModel = $equipmentModel;
		$this->equipmentTypeModel = $equipmentTypeModel;
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
