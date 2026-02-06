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
				// @todo don't use name here, use an object and a transformer
				$badges[] = $level_achieved->name();
			}
		}

		return $badges;
	}
}
