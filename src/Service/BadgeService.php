<?php

declare(strict_types=1);

namespace Portalbox\Service;

use Portalbox\Enumeration\Permission;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Model\BadgeModel;
use Portalbox\Model\BadgeRuleModel;
use Portalbox\Session;

/**
 * Report on badges earned by users
 */
class BadgeService {
	public const ERROR_UNAUTHENTICATED_REPORT = 'You must be authenticated to report badges';
	public const ERROR_UNAUTHORIZED_REPORT = 'You are not authorized to report badges';

	public const ERROR_UNAUTHENTICATED_USER_READ = 'You must be authenticated to read a user\'s badges';
	public const ERROR_UNAUTHORIZED_USER_READ = 'You are not authorized to read the user\'s badges';

	protected Session $session;
	protected BadgeRuleModel $badgeRuleModel;
	protected BadgeModel $badgeModel;

	public function __construct(
		Session $session,
		BadgeRuleModel $badgeRuleModel,
		BadgeModel $badgeModel
	) {
		$this->session = $session;
		$this->badgeRuleModel = $badgeRuleModel;
		$this->badgeModel = $badgeModel;
	}

	/**
	 * Get the list of badges earned by the user
	 *
	 * @param int $user_id  the id of the user
	 * @return BadgeLevel[]  the badges earned by the user
	 * @throws AuthenticationException if no user is authenticated
	 * @throws AuthorizationException
	 */
	public function getBadgesForUser(int $user_id): array {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException(self::ERROR_UNAUTHENTICATED_USER_READ);
		}

		$role = $authenticatedUser->role();
		if (!$role->has_permission(Permission::READ_USER)) {
			if ($role->has_permission(Permission::READ_OWN_USER)) {
				if ($authenticatedUser->id() !== $user_id) {
					throw new AuthorizationException(self::ERROR_UNAUTHORIZED_USER_READ);
				}
			} else {
				throw new AuthorizationException(self::ERROR_UNAUTHORIZED_USER_READ);
			}
		}

		$badge_rules = $this->badgeRuleModel->search();
		$usage_by_equipment_type = $this->badgeModel->countForUser($user_id);

		return $this->calculateBadgesFromUsage($badge_rules, $usage_by_equipment_type);
	}

	/**
	 * Get the list of badges earned by active users
	 *
	 * @return array(User,BadgeLevel[])  the badges earned by active users
	 * @throws AuthenticationException if no user is authenticated
	 * @throws AuthorizationException
	 */
	public function getBadgesForActiveUsers(): array {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException(self::ERROR_UNAUTHENTICATED_REPORT);
		}

		if (!$authenticatedUser->role()->has_permission(Permission::REPORT_BADGES)) {
			throw new AuthorizationException(self::ERROR_UNAUTHORIZED_REPORT);
		}

		$badge_rules = $this->badgeRuleModel->search();
		$usage = $this->badgeModel->countForActiveUsers();

		$usage_by_user = [];
		foreach ($usage as $datum) {
			$user_id = $datum['user_id'];
			if (!array_key_exists($user_id, $usage_by_user)) {
				$usage_by_user[$user_id] = [];
				$usage_by_user[$user_id]['name'] = $datum['name'];
				$usage_by_user[$user_id]['email'] = $datum['email'];
			}

			$usage_by_user[$user_id][$datum['equipment_type_id']] = $datum['count'];
		}

		$report = [];
		foreach ($usage_by_user as $datum) {
			$report[] = [
				$datum['name'],
				$datum['email'],
				$this->calculateBadgesFromUsage($badge_rules, $datum)
			];
		}

		return $report;
	}

	/**
	 * Given a set of badge rules and the equipment usage for a user calculate
	 * the badges they have earned
	 *
	 * @return BadgeLevel[]  the badges earned by the user
	 */
	private function calculateBadgesFromUsage(
		array $badge_rules,
		array $usage_by_equipment_type
	): array {
		$badges = [];

		foreach ($badge_rules as $rule) {
			$usage = 0;
			foreach ($rule->equipment_type_ids() as $equipment_type_id) {
				$usage += $usage_by_equipment_type[$equipment_type_id] ?? 0;
			}

			$level_achieved = null;
			foreach ($rule->levels() as $level) {
				if ($usage >= $level->uses()) {
					$level_achieved = $level;
				}
			}

			if ($level_achieved) {
				$badges[] = $level_achieved;
			}
		}

		return $badges;
	}
}
