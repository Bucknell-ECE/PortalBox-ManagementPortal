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
	#region test getBadgesForUser()

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

		$badgeLevel1 = (new BadgeLevel())
			->set_name('Beginner')
			->set_image('beginner.svg')
			->set_uses(10);
		$badgeLevel2 = (new BadgeLevel())
			->set_name('Journeyman')
			->set_image('journeyman.svg')
			->set_uses(100);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);
		$badgeRuleModel->method('search')->willReturn([
			(new BadgeRule())
				->set_equipment_type_ids([2,4])
				->set_levels([
					(new BadgeLevel())
						->set_name('Novice')
						->set_image('novice.svg')
						->set_uses(10),
					$badgeLevel2,
					(new BadgeLevel())
						->set_name('Pro')
						->set_image('pro.svg')
						->set_uses(1000),
				]),
			(new BadgeRule())
				->set_equipment_type_ids([1,3])
				->set_levels([
					$badgeLevel1,
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

		self::assertIsArray($badges);
		self::assertEqualsCanonicalizing(
			[$badgeLevel1, $badgeLevel2],
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

		$badgeLevel1 = (new BadgeLevel())
			->set_name('Beginner')
			->set_image('beginner.svg')
			->set_uses(10);
		$badgeLevel2 = (new BadgeLevel())
			->set_name('Journeyman')
			->set_image('journeyman.svg')
			->set_uses(100);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);
		$badgeRuleModel->method('search')->willReturn([
			(new BadgeRule())
				->set_equipment_type_ids([2,4])
				->set_levels([
					(new BadgeLevel())
						->set_name('Novice')
						->set_image('novice.svg')
						->set_uses(10),
					$badgeLevel2,
					(new BadgeLevel())
						->set_name('Pro')
						->set_image('pro.svg')
						->set_uses(1000),
				]),
			(new BadgeRule())
				->set_equipment_type_ids([1,3])
				->set_levels([
					$badgeLevel1,
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

		self::assertIsArray($badges);
		self::assertEqualsCanonicalizing(
			[$badgeLevel1, $badgeLevel2],
			$badges
		);
	}

	#endregion test getBadgesForUser()

	#region test getBadgesForActiveUsers()

	public function testGetBadgesForActiveUsersThrowsWhenNotAuthenticated() {
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
		self::expectExceptionMessage(BadgeService::ERROR_UNAUTHENTICATED_REPORT);
		$service->getBadgesForActiveUsers();
	}

	public function testGetBadgesForActiveUsersThrowsWhenNotAuthorized() {
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
		self::expectExceptionMessage(BadgeService::ERROR_UNAUTHORIZED_REPORT);
		$service->getBadgesForActiveUsers();
	}

	public function testGetBadgesForActiveUsers() {
		$user1_name = 'Cody';
		$user1_email = 'cody@ficticious.tld';
		$user2_name = 'Sebastian';
		$user2_email = 'sebastian@ficticious.tld';

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(3)
						->set_permissions([Permission::REPORT_BADGES])
				)
		);

		$badgeModel = $this->createStub(BadgeModel::class);
		$badgeModel->method('countForActiveUsers')
			->willReturn([
				[
					'user_id' => 1,
					'name' => $user1_name,
					'email' => $user1_email,
					'equipment_type_id' => 1,
					'count' => 11
				],
				[
					'user_id' => 1,
					'name' => $user1_name,
					'email' => $user1_email,
					'equipment_type_id' => 2,
					'count' => 34
				],
				[
					'user_id' => 1,
					'name' => $user1_name,
					'email' => $user1_email,
					'equipment_type_id' => 4,
					'count' => 72
				],
				[
					'user_id' => 2,
					'name' => $user2_name,
					'email' => $user2_email,
					'equipment_type_id' => 1,
					'count' => 100
				],
				[
					'user_id' => 2,
					'name' => $user2_name,
					'email' => $user2_email,
					'equipment_type_id' => 4,
					'count' => 100
				]
			]);

		$badgeLevel1 = (new BadgeLevel())
			->set_name('Beginner')
			->set_image('beginner.svg')
			->set_uses(10);
		$badgeLevel2 = (new BadgeLevel())
			->set_name('Journeyman')
			->set_image('journeyman.svg')
			->set_uses(100);
		$badgeLevel3 = (new BadgeLevel())
			->set_name('Expert')
			->set_image('expert.svg')
			->set_uses(100);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);
		$badgeRuleModel->method('search')->willReturn([
			(new BadgeRule())
				->set_equipment_type_ids([2,4])
				->set_levels([
					(new BadgeLevel())
						->set_name('Novice')
						->set_image('novice.svg')
						->set_uses(10),
					$badgeLevel2,
					(new BadgeLevel())
						->set_name('Pro')
						->set_image('pro.svg')
						->set_uses(1000),
				]),
			(new BadgeRule())
				->set_equipment_type_ids([1,3])
				->set_levels([
					$badgeLevel1,
					$badgeLevel3
				]),
		]);

		$service = new BadgeService(
			$session,
			$badgeRuleModel,
			$badgeModel
		);

		$report = $service->getBadgesForActiveUsers();

		self::assertIsArray($report);
		self::assertCount(2, $report);

		$user1_report = null;
		$user2_report = null;
		foreach ($report as $row) {
			self::assertIsArray($row);
			self::assertCount(3, $row);

			if ($row[0] === $user1_name) {
				$user1_report = $row;
			}

			if ($row[0] === $user2_name) {
				$user2_report = $row;
			}
		}

		self::assertIsArray($user1_report);
		self::assertSame($user1_name, $user1_report[0]);
		self::assertSame($user1_email, $user1_report[1]);
		self::assertIsArray($user1_report[2]);
		self::assertCount(2, $user1_report[2]);
		self::assertContains($badgeLevel1, $user1_report[2]);
		self::assertContains($badgeLevel2, $user1_report[2]);

		self::assertIsArray($user2_report);
		self::assertSame($user2_name, $user2_report[0]);
		self::assertSame($user2_email, $user2_report[1]);
		self::assertIsArray($user2_report[2]);
		self::assertCount(2, $user2_report[2]);
		self::assertContains($badgeLevel2, $user2_report[2]);
		self::assertContains($badgeLevel3, $user2_report[2]);
	}

	#endregion test getBadgesForActiveUsers()
}
