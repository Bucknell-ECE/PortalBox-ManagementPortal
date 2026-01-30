<?php

declare(strict_types=1);

namespace Test\Portalbox\Service;

use DateInterval;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Portalbox\Entity\Permission;
use Portalbox\Entity\Role;
use Portalbox\Entity\User;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Model\LoggedEventModel;
use Portalbox\Service\LoggedEventService;
use Portalbox\Session;

final class LoggedEventServiceTest extends TestCase {
	#region test getUsageStatsForEquipment

	public function testGetUsageStatsForEquipmentThrowsWhenNotAuthenticated() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new LoggedEventService(
			$session,
			$loggedEventModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(LoggedEventService::ERROR_UNAUTHENTICATED);
		$service->getUsageStatsForEquipment(85);
	}

	public function testGetUsageStatsForEquipmentThrowsWhenNotAuthorized() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new LoggedEventService(
			$session,
			$loggedEventModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(LoggedEventService::ERROR_UNAUTHORIZED_READ_OF_STATISTICS);
		$service->getUsageStatsForEquipment(85);
	}

	public function testGetUsageStatsForEquipmentSuccess() {
		$now = new DateTimeImmutable();
		$sparseCounts = [
			$now->sub(new DateInterval('P25D'))->format('Y-m-d') => 5,
			$now->sub(new DateInterval('P19D'))->format('Y-m-d') => 15,
			$now->sub(new DateInterval('P14D'))->format('Y-m-d') => 3,
			$now->sub(new DateInterval('P11D'))->format('Y-m-d') => 10,
			$now->sub(new DateInterval('P7D'))->format('Y-m-d') => 11,
			$now->sub(new DateInterval('P4D'))->format('Y-m-d') => 7
		];

		$counts = [];
		$day = $now->sub(new DateInterval('P30D'));
		for ($i = 0; $i <= 30; $i++) {
			$dateString = $day->format('Y-m-d');
			if (array_key_exists($dateString, $sparseCounts)) {
				$counts[$dateString] = $sparseCounts[$dateString];
			} else {
				$counts[$dateString] = 0;
			}

			$day = $day->add(new DateInterval('P1D'));
		}

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::READ_EQUIPMENT])
				)
		);

		$loggedEventModel = $this->createStub(LoggedEventModel::class);
		$loggedEventModel->method('count')->willReturn($sparseCounts);

		$service = new LoggedEventService(
			$session,
			$loggedEventModel
		);

		self::assertSame(
			$counts,
			$service->getUsageStatsForEquipment(85)
		);
	}

	#endregion test getUsageStatsForEquipment

	#region test getUsageStatsForLocation

	public function testGetUsageStatsForLocationThrowsWhenNotAuthenticated() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new LoggedEventService(
			$session,
			$loggedEventModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(LoggedEventService::ERROR_UNAUTHENTICATED);
		$service->getUsageStatsForLocation(85);
	}

	public function testGetUsageStatsForLocationThrowsWhenNotAuthorized() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new LoggedEventService(
			$session,
			$loggedEventModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(LoggedEventService::ERROR_UNAUTHORIZED_READ_OF_STATISTICS);
		$service->getUsageStatsForLocation(85);
	}

	public function testGetUsageStatsForLocationSuccess() {
		$now = new DateTimeImmutable();
		$sparseCounts = [
			$now->sub(new DateInterval('P25D'))->format('Y-m-d') => 5,
			$now->sub(new DateInterval('P19D'))->format('Y-m-d') => 15,
			$now->sub(new DateInterval('P14D'))->format('Y-m-d') => 3,
			$now->sub(new DateInterval('P11D'))->format('Y-m-d') => 10,
			$now->sub(new DateInterval('P7D'))->format('Y-m-d') => 11,
			$now->sub(new DateInterval('P4D'))->format('Y-m-d') => 7
		];

		$counts = [];
		$day = $now->sub(new DateInterval('P30D'));
		for ($i = 0; $i <= 30; $i++) {
			$dateString = $day->format('Y-m-d');
			if (array_key_exists($dateString, $sparseCounts)) {
				$counts[$dateString] = $sparseCounts[$dateString];
			} else {
				$counts[$dateString] = 0;
			}

			$day = $day->add(new DateInterval('P1D'));
		}

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::READ_LOCATION])
				)
		);

		$loggedEventModel = $this->createStub(LoggedEventModel::class);
		$loggedEventModel->method('count')->willReturn($sparseCounts);

		$service = new LoggedEventService(
			$session,
			$loggedEventModel
		);

		self::assertSame(
			$counts,
			$service->getUsageStatsForLocation(85)
		);
	}

	public function testGetUsageStatsForAllLocationsSuccess() {
		$now = new DateTimeImmutable();
		$sparseCounts = [
			$now->sub(new DateInterval('P25D'))->format('Y-m-d') => 5,
			$now->sub(new DateInterval('P19D'))->format('Y-m-d') => 15,
			$now->sub(new DateInterval('P14D'))->format('Y-m-d') => 3,
			$now->sub(new DateInterval('P11D'))->format('Y-m-d') => 10,
			$now->sub(new DateInterval('P7D'))->format('Y-m-d') => 11,
			$now->sub(new DateInterval('P4D'))->format('Y-m-d') => 7
		];

		$counts = [];
		$day = $now->sub(new DateInterval('P30D'));
		for ($i = 0; $i <= 30; $i++) {
			$dateString = $day->format('Y-m-d');
			if (array_key_exists($dateString, $sparseCounts)) {
				$counts[$dateString] = $sparseCounts[$dateString];
			} else {
				$counts[$dateString] = 0;
			}

			$day = $day->add(new DateInterval('P1D'));
		}

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$loggedEventModel = $this->createStub(LoggedEventModel::class);
		$loggedEventModel->method('count')->willReturn($sparseCounts);

		$service = new LoggedEventService(
			$session,
			$loggedEventModel
		);

		self::assertSame(
			$counts,
			$service->getUsageStatsForLocation()
		);
	}

	#endregion test getUsageStatsForLocation
}
