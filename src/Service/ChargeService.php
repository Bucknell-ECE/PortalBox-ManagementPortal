<?php

declare(strict_types=1);

namespace Portalbox\Service;

use DateTimeImmutable;
use InvalidArgumentException;
use Portalbox\Enumeration\Permission;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Exception\NotFoundException;
use Portalbox\Model\ChargeModel;
use Portalbox\Model\EquipmentModel;
use Portalbox\Query\ChargeQuery;
use Portalbox\Session;
use Portalbox\Type\Charge;

class ChargeService {
	public const ERROR_UNAUTHENTICATED_READ = 'You must be authenticated to read charges';
	public const ERROR_UNAUTHORIZED_READ = 'You are not authorized to read charges';
	public const ERROR_CHARGE_NOT_FOUND = 'We have no record of that charge';

	public const ERROR_USER_FILTER_MUST_BE_INT = 'The value of user_id must be an integer';

	protected Session $session;
	protected ChargeModel $chargeModel;
	protected EquipmentModel $equipmentModel;

	public function __construct(
		Session $session,
		ChargeModel $chargeModel,
		EquipmentModel $equipmentModel
	) {
		$this->session = $session;
		$this->chargeModel = $chargeModel;
		$this->equipmentModel = $equipmentModel;
	}

	/**
	 * Read a charge by id
	 *
	 * @param int $id  the unique id of the charge to read
	 * @return Location  the charge
	 * @throws AuthenticationException  if no user is authenticated
	 * @throws AuthorizationException  if the authenticated user may not read
	 *      the charge
	 * @throws NotFoundException  if the charge is not found
	 */
	public function read(int $id): Charge {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException(self::ERROR_UNAUTHENTICATED_READ);
		}

		if (!$authenticatedUser->role()->has_permission(Permission::READ_CHARGE)) {
			throw new AuthorizationException(self::ERROR_UNAUTHORIZED_READ);
		}

		$charge = $this->chargeModel->read($id);
		if ($charge === null) {
			throw new NotFoundException(self::ERROR_CHARGE_NOT_FOUND);
		}

		return $charge;
	}

	/**
	 * Read all charges
	 *
	 * @param array<string, string>  filters that all payments in the result set
	 *      must match
	 * @return Charge[]  the charges
	 * @throws AuthenticationException  if no user is authenticated
	 * @throws AuthorizationException  if the authenticated user may not read
	 *      the charges
	 */
	public function readAll(array $filters): array {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException(self::ERROR_UNAUTHENTICATED_READ);
		}

		$role = $authenticatedUser->role();
		if (!$role->has_permission(Permission::LIST_CHARGES)) {
			if ($role->has_permission(Permission::LIST_OWN_CHARGES)) {
				$userId = $filters['user_id'] ?? '';
				if ($authenticatedUser->id() != $userId) {
					throw new AuthorizationException(self::ERROR_UNAUTHORIZED_READ);
				}
			} else {
				throw new AuthorizationException(self::ERROR_UNAUTHORIZED_READ);
			}
		}

		$query = new ChargeQuery();

		if (isset($filters['user_id']) && !empty($filters['user_id'])) {
			$user_id = filter_var($filters['user_id'], FILTER_VALIDATE_INT);
			if ($user_id === false) {
				throw new InvalidArgumentException(self::ERROR_USER_FILTER_MUST_BE_INT);
			}

			$query->set_user_id($user_id);
		}

		if (isset($filters['after']) && !empty($filters['after'])) {
			$after = new DateTimeImmutable($filters['after']);
			$query->set_on_or_after($after);
		}

		if (isset($filters['before']) && !empty($filters['before'])) {
			$before = new DateTimeImmutable($filters['before']);
			$query->set_on_or_before($before);
		}

		$charges = $this->chargeModel->search($query);

		// We want to include equipment details in the charges but do not want
		// the overhead of a database access for each charge via the injected model
		// so we'll read all equipment quick and stick equipment into the charges
		$equipment_lookup_table = [];
		foreach ($this->equipmentModel->search() as $equipment) {
			$equipment_lookup_table[$equipment->id()] = $equipment;
		}

		foreach ($charges as $charge) {
			$charge->set_equipment(
				$equipment_lookup_table[$charge->equipment_id()]
			);
		}

		return $charges;
	}
}
