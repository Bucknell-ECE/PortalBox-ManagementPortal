<?php

declare(strict_types=1);

namespace Test\Portalbox\Service;

use DateInterval;
use DateTimeImmutable;
use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Portalbox\Enumeration\Permission;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Model\LoggedEventModel;
use Portalbox\Query\LoggedEventQuery;
use Portalbox\Service\LoggedEventService;
use Portalbox\Session;
use Portalbox\Type\LoggedEvent;
use Portalbox\Type\Role;
use Portalbox\Type\User;

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

	#region test readAll

	public function testReadAllThrowsWhenNotAuthenticated() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new LoggedEventService(
			$session,
			$loggedEventModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(LoggedEventService::ERROR_UNAUTHENTICATED_READ);
		$service->readAll([]);
	}

	public function testReadAllThrowsWhenNotAuthorized() {
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
		self::expectExceptionMessage(LoggedEventService::ERROR_UNAUTHORIZED_READ);
		$service->readAll([]);
	}

	public function testReadAllThrowsWhenFiltersIncludeInvalidEquipmentId() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::LIST_LOGS])
				)
		);

		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new LoggedEventService(
			$session,
			$loggedEventModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(LoggedEventService::ERROR_EQUIPMENT_FILTER_MUST_BE_INT);
		$service->readAll(['equipment_id' => 'cats!']);
	}

	public function testReadAllThrowsWhenFiltersIncludeInvalidEquipmentTypeId() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::LIST_LOGS])
				)
		);

		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new LoggedEventService(
			$session,
			$loggedEventModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(LoggedEventService::ERROR_EQUIPMENT_TYPE_FILTER_MUST_BE_INT);
		$service->readAll(['equipment_type_id' => 'cats!']);
	}

	public function testReadAllThrowsWhenFiltersIncludeInvalidLocationId() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::LIST_LOGS])
				)
		);

		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new LoggedEventService(
			$session,
			$loggedEventModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(LoggedEventService::ERROR_LOCATION_FILTER_MUST_BE_INT);
		$service->readAll(['location_id' => 'cats!']);
	}

	public function testReadAllThrowsWhenFiltersIncludeInvalidAfter() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::LIST_LOGS])
				)
		);

		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new LoggedEventService(
			$session,
			$loggedEventModel
		);

		self::expectException(Exception::class);
		// we rely on PHP's exception message which can change without notice so no assertion
		$service->readAll(['after' => 'cats!']);
	}

	public function testReadAllThrowsWhenFiltersIncludeInvalidBefore() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::LIST_LOGS])
				)
		);

		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new LoggedEventService(
			$session,
			$loggedEventModel
		);

		self::expectException(Exception::class);
		// we rely on PHP's exception message which can change without notice so no assertion
		$service->readAll(['before' => 'cats!']);
	}

	public function testReadAllSuccess() {
		$equipment_id = 2;
		$equipment_type_id = 3;
		$location_id = 4;
		$after = '2025-03-01';
		$before = '2026-03-19';
		$log = [new LoggedEvent()];

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::LIST_LOGS])
				)
		);

		$loggedEventModel = $this->createMock(LoggedEventModel::class);
		$loggedEventModel->expects($this->once())->method('search')->with(
			$this->callback(
				fn(LoggedEventQuery $query) =>
					$query->equipment_id() === $equipment_id
					&& $query->equipment_type_id() === $equipment_type_id
					&& $query->location_id() === $location_id
					&& $query->on_or_after()->format('Y-m-d') === $after
					&& $query->on_or_before()->format('Y-m-d') === $before
			)
		)->willReturn($log);

		$service = new LoggedEventService(
			$session,
			$loggedEventModel
		);

		self::assertSame(
			$log,
			$service->readAll([
				'equipment_id' => $equipment_id,
				'equipment_type_id' => $equipment_type_id,
				'location_id' => $location_id,
				'after' => $after,
				'before' => $before
			])
		);
	}

	#endregion test readAll
}
