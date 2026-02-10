<?php

declare(strict_types=1);

namespace Test\Portalbox\Service;

use PHPUnit\Framework\TestCase;
use Portalbox\Enumeration\Permission;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Model\BadgeModel;
use Portalbox\Model\BadgeRuleModel;
use Portalbox\Service\BadgeService;
use Portalbox\Session;
use Portalbox\Type\BadgeLevel;
use Portalbox\Type\BadgeRule;
use Portalbox\Type\Role;
use Portalbox\Type\User;

final class BadgeServiceTest extends TestCase {
	public function testGetBadgesForUserThrowsWhenNotAuthenticated() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$badgeModel = $this->createStub(BadgeModel::class);
		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);

		$service = new BadgeService(
			$session,
			$badgeRuleModel,
			$badgeModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(BadgeService::ERROR_UNAUTHENTICATED_USER_READ);
		$service->getBadgesForUser(23);
	}

	public function testGetBadgesForUserThrowsWhenNotAuthorized() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_id(12)
				->set_role((new Role())->set_id(2))
		);

		$badgeModel = $this->createStub(BadgeModel::class);
		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);

		$service = new BadgeService(
			$session,
			$badgeRuleModel,
			$badgeModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(BadgeService::ERROR_UNAUTHORIZED_USER_READ);
		$service->getBadgesForUser(23);
	}

	public function testGetBadgesForUserAsAdmin() {
		$user_id = 23;

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::READ_USER])
				)
		);

		$badgeModel = $this->createMock(BadgeModel::class);
		$badgeModel->expects($this->once())->method('countForUser')
			->with($user_id)
			->willReturn([
				1 => 11,
				2 => 34,
				4 => 72
			]);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);
		$badgeRuleModel->method('search')->willReturn([
			(new BadgeRule())
				->set_equipment_type_ids([2,4])
				->set_levels([
					(new BadgeLevel())
						->set_name('Novice')
						->set_image('novice.svg')
						->set_uses(10),
					(new BadgeLevel())
						->set_name('Journeyman')
						->set_image('journeyman.svg')
						->set_uses(100),
					(new BadgeLevel())
						->set_name('Pro')
						->set_image('pro.svg')
						->set_uses(1000),
				]),
			(new BadgeRule())
				->set_equipment_type_ids([1,3])
				->set_levels([
					(new BadgeLevel())
						->set_name('Beginner')
						->set_image('beginner.svg')
						->set_uses(10),
					(new BadgeLevel())
						->set_name('Expert')
						->set_image('expert.svg')
						->set_uses(100),
				]),
		]);

		$service = new BadgeService(
			$session,
			$badgeRuleModel,
			$badgeModel
		);

		$badges = $service->getBadgesForUser($user_id);

		self::assertCount(2, $badges);
		self::assertEqualsCanonicalizing(
			['Beginner', 'Journeyman'],
			$badges
		);
	}

	public function testGetBadgesForUserAsUserForSelf() {
		$user_id = 23;

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_id($user_id)
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::READ_OWN_USER])
				)
		);

		$badgeModel = $this->createMock(BadgeModel::class);
		$badgeModel->expects($this->once())->method('countForUser')
			->with($user_id)
			->willReturn([
				1 => 11,
				2 => 34,
				4 => 72
			]);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);
		$badgeRuleModel->method('search')->willReturn([
			(new BadgeRule())
				->set_equipment_type_ids([2,4])
				->set_levels([
					(new BadgeLevel())
						->set_name('Novice')
						->set_image('novice.svg')
						->set_uses(10),
					(new BadgeLevel())
						->set_name('Journeyman')
						->set_image('journeyman.svg')
						->set_uses(100),
					(new BadgeLevel())
						->set_name('Pro')
						->set_image('pro.svg')
						->set_uses(1000),
				]),
			(new BadgeRule())
				->set_equipment_type_ids([1,3])
				->set_levels([
					(new BadgeLevel())
						->set_name('Beginner')
						->set_image('beginner.svg')
						->set_uses(10),
					(new BadgeLevel())
						->set_name('Expert')
						->set_image('expert.svg')
						->set_uses(100),
				]),
		]);

		$service = new BadgeService(
			$session,
			$badgeRuleModel,
			$badgeModel
		);

		$badges = $service->getBadgesForUser($user_id);

		self::assertCount(2, $badges);
		self::assertEqualsCanonicalizing(
			['Beginner', 'Journeyman'],
			$badges
		);
	}
}
