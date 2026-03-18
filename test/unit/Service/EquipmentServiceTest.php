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
use Portalbox\Model\LocationModel;
use Portalbox\Query\EquipmentQuery;
use Portalbox\Service\EquipmentService;
use Portalbox\Session;
use Portalbox\Type\Equipment;
use Portalbox\Type\EquipmentType;
use Portalbox\Type\Location;
use Portalbox\Type\Role;
use Portalbox\Type\User;

final class EquipmentServiceTest extends TestCase {
	#region test create()

	public function testCreateThrowsWhenNotAuthenticated() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);

		$service = new EquipmentService(
			$session,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_UNAUTHENTICATED_CREATE);
		$service->create('not a file path');
	}

	public function testCreateThrowsWhenNotAuthorized() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);

		$service = new EquipmentService(
			$session,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_UNAUTHORIZED_CREATE);
		$service->create('not a file path');
	}

	public function testCreateThrowsWhenFileIsNotReadable() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_EQUIPMENT])
				)
		);

		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);

		$service = new EquipmentService(
			$session,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_INVALID_EQUIPMENT_DATA);
		// PHP warning is intentionally suppressed in next line for testing
		@$service->create('file_does_not_exist.json');
	}

	public function testCreateThrowsWhenDataIsNotArray() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_EQUIPMENT])
				)
		);

		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);

		$service = new EquipmentService(
			$session,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_INVALID_EQUIPMENT_DATA);
		$service->create(realpath(__DIR__ . '/EquipmentServiceTestData/DataIsNotArray.json'));
	}

	public function testCreateThrowsWhenNameIsMissing() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_EQUIPMENT])
				)
		);

		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);

		$service = new EquipmentService(
			$session,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_NAME_IS_REQUIRED);
		$service->create(realpath(__DIR__ . '/EquipmentServiceTestData/NameIsMissing.json'));
	}

	public function testCreateThrowsWhenNameIsInvalid() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_EQUIPMENT])
				)
		);

		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);

		$service = new EquipmentService(
			$session,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_NAME_IS_REQUIRED);
		$service->create(realpath(__DIR__ . '/EquipmentServiceTestData/NameIsInvalid.json'));
	}

	public function testCreateThrowsWhenTypeIdIsMissing() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_EQUIPMENT])
				)
		);

		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);

		$service = new EquipmentService(
			$session,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_TYPE_ID_IS_REQUIRED);
		$service->create(realpath(__DIR__ . '/EquipmentServiceTestData/TypeIdIsMissing.json'));
	}

	public function testCreateThrowsWhenTypeIdIsNotInt() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_EQUIPMENT])
				)
		);

		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);

		$service = new EquipmentService(
			$session,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_TYPE_ID_IS_REQUIRED);
		$service->create(realpath(__DIR__ . '/EquipmentServiceTestData/TypeIdIsNotInt.json'));
	}

	public function testCreateThrowsWhenTypeIdIsInvalid() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_EQUIPMENT])
				)
		);

		$equipmentModel = $this->createStub(EquipmentModel::class);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('read')->willReturn(NULL);

		$locationModel = $this->createStub(LocationModel::class);

		$service = new EquipmentService(
			$session,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_INVALID_TYPE_ID);
		$service->create(realpath(__DIR__ . '/EquipmentServiceTestData/ValidEquipment.json'));
	}

	public function testCreateThrowsWhenLocationIdIsMissing() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_EQUIPMENT])
				)
		);

		$equipmentModel = $this->createStub(EquipmentModel::class);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('read')->willReturn(new EquipmentType());

		$locationModel = $this->createStub(LocationModel::class);

		$service = new EquipmentService(
			$session,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_LOCATION_ID_IS_REQUIRED);
		$service->create(realpath(__DIR__ . '/EquipmentServiceTestData/LocationIdIsMissing.json'));
	}

	public function testCreateThrowsWhenLocationIdIsNotInt() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_EQUIPMENT])
				)
		);

		$equipmentModel = $this->createStub(EquipmentModel::class);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('read')->willReturn(new EquipmentType());

		$locationModel = $this->createStub(LocationModel::class);

		$service = new EquipmentService(
			$session,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_LOCATION_ID_IS_REQUIRED);
		$service->create(realpath(__DIR__ . '/EquipmentServiceTestData/LocationIdIsNotInt.json'));
	}

	public function testCreateThrowsWhenLocationIdIsInvalid() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_EQUIPMENT])
				)
		);

		$equipmentModel = $this->createStub(EquipmentModel::class);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('read')->willReturn(new EquipmentType());

		$locationModel = $this->createStub(LocationModel::class);
		$locationModel->method('read')->willReturn(NULL);

		$service = new EquipmentService(
			$session,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_INVALID_LOCATION_ID);
		$service->create(realpath(__DIR__ . '/EquipmentServiceTestData/ValidEquipment.json'));
	}

	public function testCreateThrowsWhenMACAddressIsMissing() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_EQUIPMENT])
				)
		);

		$equipmentModel = $this->createStub(EquipmentModel::class);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('read')->willReturn(new EquipmentType());

		$locationModel = $this->createStub(LocationModel::class);
		$locationModel->method('read')->willReturn(new Location());

		$service = new EquipmentService(
			$session,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_MAC_ADDRESS_IS_REQUIRED);
		$service->create(realpath(__DIR__ . '/EquipmentServiceTestData/MACAddressIsMissing.json'));
	}

	public function testCreateThrowsWhenMACAddressIsInvalid() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_EQUIPMENT])
				)
		);

		$equipmentModel = $this->createStub(EquipmentModel::class);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('read')->willReturn(new EquipmentType());

		$locationModel = $this->createStub(LocationModel::class);
		$locationModel->method('read')->willReturn(new Location());

		$service = new EquipmentService(
			$session,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_MAC_ADDRESS_IS_REQUIRED);
		$service->create(realpath(__DIR__ . '/EquipmentServiceTestData/MACAddressIsInvalid.json'));
	}

	public function testCreateThrowsWhenTimeoutIsMissing() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_EQUIPMENT])
				)
		);

		$equipmentModel = $this->createStub(EquipmentModel::class);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('read')->willReturn(new EquipmentType());

		$locationModel = $this->createStub(LocationModel::class);
		$locationModel->method('read')->willReturn(new Location());

		$service = new EquipmentService(
			$session,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_TIMEOUT_IS_REQUIRED);
		$service->create(realpath(__DIR__ . '/EquipmentServiceTestData/TimeoutIsMissing.json'));
	}

	public function testCreateThrowsWhenTimeoutIsNotInt() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_EQUIPMENT])
				)
		);

		$equipmentModel = $this->createStub(EquipmentModel::class);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('read')->willReturn(new EquipmentType());

		$locationModel = $this->createStub(LocationModel::class);
		$locationModel->method('read')->willReturn(new Location());

		$service = new EquipmentService(
			$session,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_TIMEOUT_IS_REQUIRED);
		$service->create(realpath(__DIR__ . '/EquipmentServiceTestData/TimeoutIsNotInt.json'));
	}

	public function testCreateThrowsWhenTimeoutIsInvalid() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_EQUIPMENT])
				)
		);

		$equipmentModel = $this->createStub(EquipmentModel::class);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('read')->willReturn(new EquipmentType());

		$locationModel = $this->createStub(LocationModel::class);
		$locationModel->method('read')->willReturn(new Location());

		$service = new EquipmentService(
			$session,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_TIMEOUT_IS_REQUIRED);
		$service->create(realpath(__DIR__ . '/EquipmentServiceTestData/TimeoutIsInvalid.json'));
	}

	public function testCreateThrowsWhenInServiceIsMissing() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_EQUIPMENT])
				)
		);

		$equipmentModel = $this->createStub(EquipmentModel::class);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('read')->willReturn(new EquipmentType());

		$locationModel = $this->createStub(LocationModel::class);
		$locationModel->method('read')->willReturn(new Location());

		$service = new EquipmentService(
			$session,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_IN_SERVICE_IS_REQUIRED);
		$service->create(realpath(__DIR__ . '/EquipmentServiceTestData/InServiceIsMissing.json'));
	}

	public function testCreateThrowsWhenInServiceIsInvalid() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_EQUIPMENT])
				)
		);

		$equipmentModel = $this->createStub(EquipmentModel::class);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('read')->willReturn(new EquipmentType());

		$locationModel = $this->createStub(LocationModel::class);
		$locationModel->method('read')->willReturn(new Location());

		$service = new EquipmentService(
			$session,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_IN_SERVICE_IS_REQUIRED);
		$service->create(realpath(__DIR__ . '/EquipmentServiceTestData/InServiceIsInvalid.json'));
	}

	public function testCreateThrowsWhenServiceMinutesIsInvalid() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_EQUIPMENT])
				)
		);

		$equipmentModel = $this->createStub(EquipmentModel::class);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('read')->willReturn(new EquipmentType());

		$locationModel = $this->createStub(LocationModel::class);
		$locationModel->method('read')->willReturn(new Location());

		$service = new EquipmentService(
			$session,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_SERVICE_MINUTES_IS_INVALID);
		$service->create(realpath(__DIR__ . '/EquipmentServiceTestData/ServiceMinutesIsInvalid.json'));
	}

	public function testCreateSuccessWithServiceMinutes() {
		// values matching input file
		$in_service = true;
		$location_id = 3;
		$mac_address = 'aa00bb11cc22'; // normalized by Portalbox\Type\Equipment
		$name = 'Kibble Launcher';
		$service_minutes = 137;
		$timeout = 60;
		$type_id = 2;

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_EQUIPMENT])
				)
		);

		$equipmentModel = $this->createMock(EquipmentModel::class);
		$equipmentModel->expects($this->once())->method('create')->with(
			$this->callback(
				fn($equipment) =>
					$equipment->id() === null
					&& $equipment->name() === $name
					&& $equipment->type()->id() === $type_id
					&& $equipment->location()->id() === $location_id
					&& $equipment->mac_address() === $mac_address
					&& $equipment->timeout() === $timeout
					&& $equipment->is_in_service() === $in_service
					&& $equipment->service_minutes() === $service_minutes
			)
		)
		->willReturnArgument(0);

		$equipmentTypeModel = $this->createMock(EquipmentTypeModel::class);
		$equipmentTypeModel->expects($this->once())->method('read')
			->with($type_id)
			->willReturn(
				(new EquipmentType())->set_id($type_id)
			);

		$locationModel = $this->createMock(LocationModel::class);
		$locationModel->expects($this->once())->method('read')
			->with($location_id)
			->willReturn(
				(new Location())->set_id($location_id)
			);

		$service = new EquipmentService(
			$session,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
		);

		$equipment = $service->create(realpath(__DIR__ . '/EquipmentServiceTestData/ValidEquipmentWithServiceMinutes.json'));

		self::assertInstanceOf(Equipment::class, $equipment);
		self::assertSame($name, $equipment->name());
		self::assertInstanceOf(EquipmentType::class, $equipment->type());
		self::assertSame($type_id, $equipment->type()->id());
		self::assertInstanceOf(Location::class, $equipment->location());
		self::assertSame($location_id, $equipment->location()->id());
		self::assertSame($mac_address, $equipment->mac_address());
		self::assertSame($timeout, $equipment->timeout());
		self::assertSame($in_service, $equipment->is_in_service());
		self::assertSame($service_minutes, $equipment->service_minutes());
	}

	public function testCreateSuccess() {
		// values matching input file
		$in_service = true;
		$location_id = 3;
		$mac_address = 'aa00bb11cc22'; // normalized by Portalbox\Type\Equipment
		$name = 'Kibble Launcher';
		$service_minutes = 0;
		$timeout = 60;
		$type_id = 2;

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_EQUIPMENT])
				)
		);

		$equipmentModel = $this->createMock(EquipmentModel::class);
		$equipmentModel->expects($this->once())->method('create')->with(
			$this->callback(
				fn($equipment) =>
					$equipment->id() === null
					&& $equipment->name() === $name
					&& $equipment->type()->id() === $type_id
					&& $equipment->location()->id() === $location_id
					&& $equipment->mac_address() === $mac_address
					&& $equipment->timeout() === $timeout
					&& $equipment->is_in_service() === $in_service
					&& $equipment->service_minutes() === $service_minutes
			)
		)
		->willReturnArgument(0);

		$equipmentTypeModel = $this->createMock(EquipmentTypeModel::class);
		$equipmentTypeModel->expects($this->once())->method('read')
			->with($type_id)
			->willReturn(
				(new EquipmentType())->set_id($type_id)
			);

		$locationModel = $this->createMock(LocationModel::class);
		$locationModel->expects($this->once())->method('read')
			->with($location_id)
			->willReturn(
				(new Location())->set_id($location_id)
			);

		$service = new EquipmentService(
			$session,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
		);

		$equipment = $service->create(realpath(__DIR__ . '/EquipmentServiceTestData/ValidEquipment.json'));

		self::assertInstanceOf(Equipment::class, $equipment);
		self::assertSame($name, $equipment->name());
		self::assertInstanceOf(EquipmentType::class, $equipment->type());
		self::assertSame($type_id, $equipment->type()->id());
		self::assertInstanceOf(Location::class, $equipment->location());
		self::assertSame($location_id, $equipment->location()->id());
		self::assertSame($mac_address, $equipment->mac_address());
		self::assertSame($timeout, $equipment->timeout());
		self::assertSame($in_service, $equipment->is_in_service());
		self::assertSame($service_minutes, $equipment->service_minutes());
	}

	#endregion test create()

	#region test read()

	public function testReadThrowsWhenNotAuthenticated() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);

		$service = new EquipmentService(
			$session,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
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
		$locationModel = $this->createStub(LocationModel::class);

		$service = new EquipmentService(
			$session,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
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
		$locationModel = $this->createStub(LocationModel::class);

		$service = new EquipmentService(
			$session,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
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
		$locationModel = $this->createStub(LocationModel::class);

		$service = new EquipmentService(
			$session,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
		);

		self::assertSame($equipment, $service->read(123456789));
	}

	#endregion test read()

	#region test readAll()

	public function testReadAllThrowsWhenLocationFilterIsNotInteger() {
		$session = $this->createStub(Session::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);

		$service = new EquipmentService(
			$session,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
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
		$locationModel = $this->createStub(LocationModel::class);

		$service = new EquipmentService(
			$session,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
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
		$locationModel = $this->createStub(LocationModel::class);

		$service = new EquipmentService(
			$session,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
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
		$locationModel = $this->createStub(LocationModel::class);

		$service = new EquipmentService(
			$session,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
		);

		self::assertSame(
			$equipment,
			$service->readAll(['include_out_of_service' => 'true'])
		);
	}

	#endregion test readAll()
}
