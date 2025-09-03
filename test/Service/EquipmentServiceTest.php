<?php

declare(strict_types=1);

namespace Test\Portalbox\Service;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Portalbox\Entity\Equipment;
use Portalbox\Entity\EquipmentType;
use Portalbox\Entity\Location;
use Portalbox\Entity\Permission;
use Portalbox\Entity\Role;
use Portalbox\Entity\ShutdownCard;
use Portalbox\Entity\User;
use Portalbox\Entity\UserCard;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Model\CardModel;
use Portalbox\Model\EquipmentModel;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Model\LocationModel;
use Portalbox\Service\EquipmentService;

final class EquipmentServiceTest extends TestCase {
	#region test register()

	public function testRegisterThrowsWhenNoAuthorizationHeader() {
		$cardModel = $this->createStub(CardModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);

		$service = new EquipmentService(
			$cardModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_NO_AUTHORIZATION_HEADER);
		$service->register('00112233445566', []);
	}

	public function testRegisterThrowsWhenAuthorizationHeaderDoesNotStartWithBearer() {
		$cardModel = $this->createStub(CardModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);

		$service = new EquipmentService(
			$cardModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_INVALID_AUTHORIZATION_HEADER);
		$service->register('00112233445566', ['HTTP_AUTHORIZATION' => 'let me in']);
	}

	public function testRegisterThrowsWhenBearerTokenIsInvalid() {
		$cardModel = $this->createStub(CardModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);

		$service = new EquipmentService(
			$cardModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_INVALID_AUTHORIZATION_HEADER);
		$service->register('00112233445566', ['HTTP_AUTHORIZATION' => 'Bearer let me in']);
	}

	public function testRegisterThrowsWhenCardDoesNotExist() {
		$cardModel = $this->createStub(CardModel::class);
		$cardModel->method('read')->willReturn(null);

		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);

		$service = new EquipmentService(
			$cardModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_REGISTRATION_NOT_AUTHORIZED);
		$service->register('00112233445566', ['HTTP_AUTHORIZATION' => 'Bearer 123456789']);
	}

	public function testRegisterThrowsWhenCardIsNotUserCard() {
		$cardModel = $this->createStub(CardModel::class);
		$cardModel->method('read')->willReturn(new ShutdownCard());

		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);

		$service = new EquipmentService(
			$cardModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_REGISTRATION_NOT_AUTHORIZED);
		$service->register('00112233445566', ['HTTP_AUTHORIZATION' => 'Bearer 123456789']);
	}

	public function testRegisterThrowsWhenUserIsNotAuthorized() {
		$cardModel = $this->createStub(CardModel::class);
		$cardModel->method('read')->willReturn(
			(new UserCard())
				->set_user(
					(new User())
						->set_id(144)
						->set_role(
							(new Role())->set_id(2)
						)
				)
		);

		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);

		$service = new EquipmentService(
			$cardModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_REGISTRATION_NOT_AUTHORIZED);
		$service->register('00112233445566', ['HTTP_AUTHORIZATION' => 'Bearer 123456789']);
	}

	public function testRegisterThrowsWhenDeviceAlreadyRegistered() {
		$mac = '001122ffeedd';

		$cardModel = $this->createStub(CardModel::class);
		$cardModel->method('read')->willReturn(
			(new UserCard())
				->set_user(
					(new User())
						->set_id(144)
						->set_role(
							(new Role())
								->set_id(2)
								->set_permissions([Permission::CREATE_EQUIPMENT])
						)
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentModel
			->expects($this->once())
			->method('search')
			->with(
				$this->callback(
				fn($query) =>
					$query->exclude_out_of_service() === true
					&& $query->mac_address() === $mac
					&& $query->type() === null
					&& $query->location() === null
					&& $query->location_id() === null
					&& $query->ip_address() === null
				)
			)
			->willReturn([new Equipment()]);

		$locationModel = $this->createStub(LocationModel::class);

		$service = new EquipmentService(
			$cardModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_DEVICE_ALREADY_REGISTERED);
		$service->register($mac, ['HTTP_AUTHORIZATION' => 'Bearer 123456789']);
	}

	public function testRegisterThrowsWhenNoLocationsSetup() {
		$mac = '001122ffeedd';

		$cardModel = $this->createStub(CardModel::class);
		$cardModel->method('read')->willReturn(
			(new UserCard())
				->set_user(
					(new User())
						->set_id(144)
						->set_role(
							(new Role())
								->set_id(2)
								->set_permissions([Permission::CREATE_EQUIPMENT])
						)
				)
		);

		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$locationModel = $this->createStub(LocationModel::class);
		$locationModel->method('search')->willReturn([]);

		$service = new EquipmentService(
			$cardModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_INCOMPLETE_SETUP_NO_LOCATIONS);
		$service->register($mac, ['HTTP_AUTHORIZATION' => 'Bearer 123456789']);
	}

	public function testRegisterSuccess() {
		$mac = '001122ffeedd';
		$location_id = 12;
		$equipment_type_id = 1;

		$cardModel = $this->createStub(CardModel::class);
		$cardModel->method('read')->willReturn(
			(new UserCard())
				->set_user(
					(new User())
						->set_id(144)
						->set_role(
							(new Role())
								->set_id(2)
								->set_permissions([Permission::CREATE_EQUIPMENT])
						)
				)
		);

		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentModel
			->expects($this->once())
			->method('create')
			->willReturnArgument(0);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('search')->willReturn([
			(new EquipmentType())->set_id($equipment_type_id),
		]);

		$locationModel = $this->createStub(LocationModel::class);
		$locationModel->method('search')->willReturn([
			(new Location())->set_id($location_id),
			(new Location())->set_id($location_id + 1)
		]);

		$service = new EquipmentService(
			$cardModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel
		);

		$equipment = $service->register($mac, ['HTTP_AUTHORIZATION' => 'Bearer 123456789']);

		self::assertInstanceOf(Equipment::class, $equipment);
		self::assertSame(EquipmentService::DEFAULT_DEVICE_NAME, $equipment->name());
		self::assertSame($equipment_type_id, $equipment->type_id());
		self::assertSame($location_id, $equipment->location_id());
		self::assertSame($mac, $equipment->mac_address());
		self::assertSame(0, $equipment->timeout());
		self::assertSame(false, $equipment->is_in_service());
		self::assertSame(0, $equipment->service_minutes());
		self::assertSame(null, $equipment->ip_address());

	}

	#endregion test register()
}
