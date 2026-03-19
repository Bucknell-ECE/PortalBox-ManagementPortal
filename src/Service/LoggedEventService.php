<?php

declare(strict_types=1);

namespace Portalbox\Service;

use DateInterval;
use DateTimeImmutable;
use InvalidArgumentException;
use Portalbox\Enumeration\Permission;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Model\LoggedEventModel;
use Portalbox\Query\LoggedEventQuery;
use Portalbox\Session;

/**
 * Access Event Logs
 */
class LoggedEventService {
	public const ERROR_UNAUTHENTICATED = 'You must be authenticated to read the requested statistics';
	public const ERROR_UNAUTHORIZED_READ_OF_STATISTICS = 'You are not authorized to read the requested statistics';
	public const ERROR_UNAUTHENTICATED_READ = 'You must be authenticated to read the usage log';
	public const ERROR_UNAUTHORIZED_READ = 'You are not authorized to read the usage log';

	public const ERROR_EQUIPMENT_FILTER_MUST_BE_INT = 'The equipment filter must be the integer id of equipment';
	public const ERROR_EQUIPMENT_TYPE_FILTER_MUST_BE_INT = 'The equipment type filter must be the integer id of an equipment type';
	public const ERROR_LOCATION_FILTER_MUST_BE_INT = 'The location filter must be the integer id of a location';
	public const ERROR_BEFORE_FILTER_MUST_BE_DATE = 'The before filter must be a date';
	public const ERROR_AFTER_FILTER_MUST_BE_DATE = 'The after filter must be a date';

	protected Session $session;
	protected LoggedEventModel $loggedEventModel;

	public function __construct(
		Session $session,
		LoggedEventModel $loggedEventModel
	) {
		$this->session = $session;
		$this->loggedEventModel = $loggedEventModel;
	}

	/**
	 * Get usage statistics for the specified equipment
	 *
	 * Note the look back period of 30 days and the time slice window of 1 day
	 * are for now hard coded. In the future we should make these parameters.
	 * @todo parameterize look back period
	 * @todo parameterize time slice window
	 *
	 * @param int $equipment_id  the unique id of the equipment which statistics
	 *      are requested for.
	 * @return array  a dictionary where the keys are date strings and the values
	 *      the number of times the equipment was used on that date.
	 * @throws AuthenticationException  if no user is authenticated
	 * @throws AuthorizationException  if the authenticated user may not view
	 *      equipment details
	 */
	public function getUsageStatsForEquipment(int $equipment_id): array {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException(self::ERROR_UNAUTHENTICATED);
		}

		if (!$authenticatedUser->role()->has_permission(Permission::READ_EQUIPMENT)) {
			throw new AuthorizationException(self::ERROR_UNAUTHORIZED_READ_OF_STATISTICS);
		}

		$now = new DateTimeImmutable();
		$start = $now->sub(new DateInterval('P30D'))->setTime(0, 0);

		$counts = $this->loggedEventModel->count(
			(new LoggedEventQuery())
				->set_on_or_after($start)
				->set_equipment_id($equipment_id)
		);

		$current = $start;
		$paddedCounts = [];
		while ($current < $now) {
			$formatted = $current->format('Y-m-d');
			if (array_key_exists($formatted, $counts)) {
				$paddedCounts[$formatted] = $counts[$formatted];
			} else {
				$paddedCounts[$formatted] = 0;
			}

			$current = $current->add(new DateInterval('P1D'));
		}

		return $paddedCounts;
	}

	/**
	 * Get usage statistics for the specified location
	 *
	 * Note the look back period of 30 days and the time slice window of 1 day
	 * are for now hard coded. In the future we should make these parameters.
	 * @todo parameterize look back period
	 * @todo parameterize time slice window
	 *
	 * @todo should we have access restrictions?
	 *
	 * @param int|null $location_id  the unique id of the location which
	 *      statistics are requested for.
	 * @return array  a dictionary where the keys are date strings and the values
	 *      the number of times equipment was used in the location on that date.
	 * @throws AuthenticationException  if a location is requested and no user
	 *      is authenticated
	 * @throws AuthorizationException  if a location is requested and the
	 *      authenticated user may not read location details
	 */
	public function getUsageStatsForLocation(?int $location_id = null): array {
		$now = new DateTimeImmutable();
		$start = $now->sub(new DateInterval('P30D'))->setTime(0, 0);
		$query = (new LoggedEventQuery())->set_on_or_after($start);

		if ($location_id !== null) {
			$authenticatedUser = $this->session->get_authenticated_user();
			if ($authenticatedUser === null) {
				throw new AuthenticationException(self::ERROR_UNAUTHENTICATED);
			}

			if (!$authenticatedUser->role()->has_permission(Permission::READ_LOCATION)) {
				throw new AuthorizationException(self::ERROR_UNAUTHORIZED_READ_OF_STATISTICS);
			}

			$query->set_location_id($location_id);
		}

		$counts = $this->loggedEventModel->count($query);

		$current = $start;
		$paddedCounts = [];
		while ($current < $now) {
			$formatted = $current->format('Y-m-d');
			if (array_key_exists($formatted, $counts)) {
				$paddedCounts[$formatted] = $counts[$formatted];
			} else {
				$paddedCounts[$formatted] = 0;
			}

			$current = $current->add(new DateInterval('P1D'));
		}

		return $paddedCounts;
	}

	/**
	 * Read log entries passing the filters
	 *
	 * @param array<string, string>  filters that all log entries in the result
	 *      set must meet
	 * @return LoggedEvent[]  the log entries passing the filters
	 * @throws AuthenticationException  if no user is authenticated
	 * @throws AuthorizationException  if the authenticated user may not read
	 *      the log
	 * @throws InvalidArgumentException if the equipment, equipment type or
	 *      location filters specify a value that can not be understood as an
	 *      integer
	 * @throws Exception (PHP <= 8.2) or DateMalformedStringException (PHP > 8.2)
	 *      if the before or after filters can not be understood as dates
	 */
	public function readAll(array $filters): array {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException(self::ERROR_UNAUTHENTICATED_READ);
		}

		if (!$authenticatedUser->role()->has_permission(Permission::LIST_LOGS)) {
			throw new AuthorizationException(self::ERROR_UNAUTHORIZED_READ);
		}

		$query = new LoggedEventQuery();

		if(isset($filters['equipment_id']) && !empty($filters['equipment_id'])) {
			$equipment_id = filter_var($filters['equipment_id'], FILTER_VALIDATE_INT);
			if ($equipment_id === false) {
				throw new InvalidArgumentException(self::ERROR_EQUIPMENT_FILTER_MUST_BE_INT);
			}

			$query->set_equipment_id($equipment_id);
		}

		if(isset($filters['equipment_type_id']) && !empty($filters['equipment_type_id'])) {
			$equipment_type_id = filter_var($filters['equipment_type_id'], FILTER_VALIDATE_INT);
			if ($equipment_type_id === false) {
				throw new InvalidArgumentException(self::ERROR_EQUIPMENT_TYPE_FILTER_MUST_BE_INT);
			}

			$query->set_equipment_type_id($equipment_type_id);
		}

		if(isset($filters['location_id']) && !empty($filters['location_id'])) {
			$location_id = filter_var($filters['location_id'], FILTER_VALIDATE_INT);
			if ($location_id === false) {
				throw new InvalidArgumentException(self::ERROR_LOCATION_FILTER_MUST_BE_INT);
			}

			$query->set_location_id($location_id);
		}

		if(isset($filters['after']) && !empty($filters['after'])) {
			$after = new DateTimeImmutable($filters['after']);
			$query->set_on_or_after($after);
		}

		if(isset($filters['before']) && !empty($filters['before'])) {
			$before = new DateTimeImmutable($filters['before']);
			$query->set_on_or_before($before);
		}

		return $this->loggedEventModel->search($query);
	}
}
