<?php

declare(strict_types=1);

namespace Test\Portalbox\Service;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Portalbox\Enumeration\Permission;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Exception\NotFoundException;
use Portalbox\Model\EquipmentModel;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Query\EquipmentQuery;
use Portalbox\Service\EquipmentService;
use Portalbox\Session;
use Portalbox\Type\Equipment;
use Portalbox\Type\Role;
use Portalbox\Type\User;

final class EquipmentServiceTest extends TestCase {
	#region test read()

	public function testReadThrowsWhenNotAuthenticated() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new EquipmentService(
			$session,
			$equipmentModel,
			$equipmentTypeModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_UNAUTHENTICATED_READ);
		$service->read(123456789);
	}

	public function testReadThrowsWhenNotAuthorized() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new EquipmentService(
			$session,
			$equipmentModel,
			$equipmentTypeModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_UNAUTHORIZED_READ);
		$service->read(123456789);
	}

	public function testReadThrowsWhenEquipmentDoesNotExist() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::READ_EQUIPMENT])
				)
		);

		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentModel->method('read')->willReturn(null);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new EquipmentService(
			$session,
			$equipmentModel,
			$equipmentTypeModel
		);

		self::expectException(NotFoundException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_EQUIPMENT_NOT_FOUND);
		$service->read(123456789);
	}

	public function testReadSuccess() {
		$equipment = new Equipment();

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::READ_EQUIPMENT])
				)
		);

		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentModel->method('read')->willReturn($equipment);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new EquipmentService(
			$session,
			$equipmentModel,
			$equipmentTypeModel
		);

		self::assertSame($equipment, $service->read(123456789));
	}

	#endregion test read()

	#region test readAll()

	public function testReadAllThrowsWhenLocationFilterIsNotInteger() {
		$session = $this->createStub(Session::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new EquipmentService(
			$session,
			$equipmentModel,
			$equipmentTypeModel,
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_LOCATION_FILTER_MUST_BE_INT);
		$service->readAll(['location_id' => 'meh']);
	}

	public function testReadAllSuccessWithNoFilters() {
		$equipment = [
			new Equipment()
		];

		$session = $this->createStub(Session::class);

		$equipmentModel = $this->createMock(EquipmentModel::class);
		$equipmentModel->expects($this->once())->method('search')->with(
			$this->callback(
				fn(EquipmentQuery $query) =>
					$query->location_id() === null
					&& $query->exclude_out_of_service()
			)
		)
		->willReturn($equipment);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new EquipmentService(
			$session,
			$equipmentModel,
			$equipmentTypeModel
		);

		self::assertSame(
			$equipment,
			$service->readAll([])
		);
	}

	public function testReadAllSuccessForSelectLocation() {
		$location_id = 2;

		$equipment = [
			new Equipment()
		];

		$session = $this->createStub(Session::class);

		$equipmentModel = $this->createMock(EquipmentModel::class);
		$equipmentModel->expects($this->once())->method('search')->with(
			$this->callback(
				fn(EquipmentQuery $query) =>
					$query->location_id() === $location_id
					&& $query->exclude_out_of_service()
			)
		)
		->willReturn($equipment);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new EquipmentService(
			$session,
			$equipmentModel,
			$equipmentTypeModel
		);

		self::assertSame(
			$equipment,
			$service->readAll(['location_id' => "$location_id"])
		);
	}

	public function testReadAllSuccessIncludingOutOfService() {
		$equipment = [
			new Equipment()
		];

		$session = $this->createStub(Session::class);

		$equipmentModel = $this->createMock(EquipmentModel::class);
		$equipmentModel->expects($this->once())->method('search')->with(
			$this->callback(
				fn(EquipmentQuery $query) =>
					$query->location_id() === null
					&& !$query->exclude_out_of_service()
			)
		)
		->willReturn($equipment);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new EquipmentService(
			$session,
			$equipmentModel,
			$equipmentTypeModel
		);

		self::assertSame(
			$equipment,
			$service->readAll(['include_out_of_service' => 'true'])
		);
	}

	#endregion test readAll()
}
