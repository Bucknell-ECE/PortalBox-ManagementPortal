<?php

declare(strict_types=1);

namespace Portalbox\Service;

use DateInterval;
use DateTimeImmutable;
use Portalbox\Entity\Permission;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Model\LoggedEventModel;
use Portalbox\Query\LoggedEventQuery;
use Portalbox\Session\SessionInterface;

/**
 * Access Event Logs
 */
class LoggedEventService {
	public const ERROR_UNAUTHENTICATED = 'You must be authenticated to access logs and statistics';
	public const ERROR_UNAUTHORIZED_READ_OF_EQUIPMENT_STATISTICS = 'You are not authorized to view usage statistics';

	protected SessionInterface $session;
	protected LoggedEventModel $loggedEventModel;

	public function __construct(
		SessionInterface $session,
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
	 * @throws AuthorizationException  if the authenticated user may not read
	 *      all equipment types
	 */
	public function getUsageStatsForEquipment(int $equipment_id): array {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException(self::ERROR_UNAUTHENTICATED);
		}

		if (!$authenticatedUser->role()->has_permission(Permission::READ_EQUIPMENT)) {
			throw new AuthorizationException(self::ERROR_UNAUTHORIZED_READ_OF_EQUIPMENT_STATISTICS);
		}

		$now = new DateTimeImmutable();
		$start = $now->sub(new DateInterval('P30D'))->setTime(0, 0);

		$counts = $this->loggedEventModel->count(
			(new LoggedEventQuery())
				->set_on_or_after(
					$start->format('Y-m-d')
				)
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
}
