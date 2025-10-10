<?php

declare(strict_types=1);

namespace Test\Portalbox\Service;

use DateInterval;
use DateTimeImmutable;
use InvalidArgumentException;
use PDO;
use PHPUnit\Framework\TestCase;
use Portalbox\Config;
use Portalbox\Entity\Charge;
use Portalbox\Entity\ChargePolicy;
use Portalbox\Entity\Equipment;
use Portalbox\Entity\EquipmentType;
use Portalbox\Entity\Location;
use Portalbox\Entity\LoggedEvent;
use Portalbox\Entity\LoggedEventType;
use Portalbox\Entity\Permission;
use Portalbox\Entity\Role;
use Portalbox\Entity\ShutdownCard;
use Portalbox\Entity\User;
use Portalbox\Entity\UserCard;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Exception\NotFoundException;
use Portalbox\Model\ActivationModel;
use Portalbox\Model\CardModel;
use Portalbox\Model\ChargeModel;
use Portalbox\Model\EquipmentModel;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Model\LocationModel;
use Portalbox\Model\LoggedEventModel;
use Portalbox\Query\EquipmentQuery;
use Portalbox\Service\EquipmentService;

final class EquipmentServiceTest extends TestCase {
	#region test register()

	public function testRegisterThrowsWhenNoAuthorizationHeader() {
		$activationModel = $this->createStub(ActivationModel::class);
		$cardModel = $this->createStub(CardModel::class);
		$chargeModel = $this->createStub(ChargeModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);
		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new EquipmentService(
			$activationModel,
			$cardModel,
			$chargeModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel,
			$loggedEventModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_NO_AUTHORIZATION_HEADER);
		$service->register('00112233445566', []);
	}

	public function testRegisterThrowsWhenAuthorizationHeaderDoesNotStartWithBearer() {
		$activationModel = $this->createStub(ActivationModel::class);
		$cardModel = $this->createStub(CardModel::class);
		$chargeModel = $this->createStub(ChargeModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);
		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new EquipmentService(
			$activationModel,
			$cardModel,
			$chargeModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel,
			$loggedEventModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_INVALID_AUTHORIZATION_HEADER);
		$service->register('00112233445566', ['HTTP_AUTHORIZATION' => 'let me in']);
	}

	public function testRegisterThrowsWhenBearerTokenIsInvalid() {
		$activationModel = $this->createStub(ActivationModel::class);
		$cardModel = $this->createStub(CardModel::class);
		$chargeModel = $this->createStub(ChargeModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);
		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new EquipmentService(
			$activationModel,
			$cardModel,
			$chargeModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel,
			$loggedEventModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_INVALID_AUTHORIZATION_HEADER);
		$service->register('00112233445566', ['HTTP_AUTHORIZATION' => 'Bearer let me in']);
	}

	public function testRegisterThrowsWhenCardDoesNotExist() {
		$activationModel = $this->createStub(ActivationModel::class);

		$cardModel = $this->createStub(CardModel::class);
		$cardModel->method('read')->willReturn(null);

		$chargeModel = $this->createStub(ChargeModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);
		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new EquipmentService(
			$activationModel,
			$cardModel,
			$chargeModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel,
			$loggedEventModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_REGISTRATION_NOT_AUTHORIZED);
		$service->register('00112233445566', ['HTTP_AUTHORIZATION' => 'Bearer 123456789']);
	}

	public function testRegisterThrowsWhenCardIsNotUserCard() {
		$activationModel = $this->createStub(ActivationModel::class);

		$cardModel = $this->createStub(CardModel::class);
		$cardModel->method('read')->willReturn(new ShutdownCard());

		$chargeModel = $this->createStub(ChargeModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);
		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new EquipmentService(
			$activationModel,
			$cardModel,
			$chargeModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel,
			$loggedEventModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_REGISTRATION_NOT_AUTHORIZED);
		$service->register('00112233445566', ['HTTP_AUTHORIZATION' => 'Bearer 123456789']);
	}

	public function testRegisterThrowsWhenUserIsNotAuthorized() {
		$activationModel = $this->createStub(ActivationModel::class);

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

		$chargeModel = $this->createStub(ChargeModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);
		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new EquipmentService(
			$activationModel,
			$cardModel,
			$chargeModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel,
			$loggedEventModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_REGISTRATION_NOT_AUTHORIZED);
		$service->register('00112233445566', ['HTTP_AUTHORIZATION' => 'Bearer 123456789']);
	}

	public function testRegisterThrowsWhenDeviceAlreadyRegistered() {
		$mac = '001122ffeedd';

		$activationModel = $this->createStub(ActivationModel::class);

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

		$chargeModel = $this->createStub(ChargeModel::class);
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
		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new EquipmentService(
			$activationModel,
			$cardModel,
			$chargeModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel,
			$loggedEventModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_DEVICE_ALREADY_REGISTERED);
		$service->register($mac, ['HTTP_AUTHORIZATION' => 'Bearer 123456789']);
	}

	public function testRegisterThrowsWhenNoLocationsSetup() {
		$mac = '001122ffeedd';

		$activationModel = $this->createStub(ActivationModel::class);

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

		$chargeModel = $this->createStub(ChargeModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$locationModel = $this->createStub(LocationModel::class);
		$locationModel->method('search')->willReturn([]);

		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new EquipmentService(
			$activationModel,
			$cardModel,
			$chargeModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel,
			$loggedEventModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_INCOMPLETE_SETUP_NO_LOCATIONS);
		$service->register($mac, ['HTTP_AUTHORIZATION' => 'Bearer 123456789']);
	}

	public function testRegisterSuccess() {
		$mac = '001122ffeedd';
		$location_id = 12;
		$equipment_type_id = 1;

		$activationModel = $this->createStub(ActivationModel::class);

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

		$chargeModel = $this->createStub(ChargeModel::class);

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

		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new EquipmentService(
			$activationModel,
			$cardModel,
			$chargeModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel,
			$loggedEventModel
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

	#region test activate()

	public function testActivateThrowsWhenNoAuthorizationHeader() {
		$activationModel = $this->createStub(ActivationModel::class);
		$cardModel = $this->createStub(CardModel::class);
		$chargeModel = $this->createStub(ChargeModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);
		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new EquipmentService(
			$activationModel,
			$cardModel,
			$chargeModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel,
			$loggedEventModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_NO_AUTHORIZATION_HEADER);
		$service->activate('00112233445566', []);
	}

	public function testActivateThrowsWhenAuthorizationHeaderDoesNotStartWithBearer() {
		$activationModel = $this->createStub(ActivationModel::class);
		$cardModel = $this->createStub(CardModel::class);
		$chargeModel = $this->createStub(ChargeModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);
		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new EquipmentService(
			$activationModel,
			$cardModel,
			$chargeModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel,
			$loggedEventModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_INVALID_AUTHORIZATION_HEADER);
		$service->activate('00112233445566', ['HTTP_AUTHORIZATION' => 'let me in']);
	}

	public function testActivateThrowsWhenBearerTokenIsInvalid() {
		$activationModel = $this->createStub(ActivationModel::class);
		$cardModel = $this->createStub(CardModel::class);
		$chargeModel = $this->createStub(ChargeModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);
		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new EquipmentService(
			$activationModel,
			$cardModel,
			$chargeModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel,
			$loggedEventModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_INVALID_AUTHORIZATION_HEADER);
		$service->activate('00112233445566', ['HTTP_AUTHORIZATION' => 'Bearer let me in']);
	}

	public function testActivateThrowsWhenCardDoesNotExist() {
		$activationModel = $this->createStub(ActivationModel::class);

		$cardModel = $this->createStub(CardModel::class);
		$cardModel->method('read')->willReturn(null);

		$chargeModel = $this->createStub(ChargeModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);
		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new EquipmentService(
			$activationModel,
			$cardModel,
			$chargeModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel,
			$loggedEventModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_ACTIVATION_NOT_AUTHORIZED);
		$service->activate('00112233445566', ['HTTP_AUTHORIZATION' => 'Bearer 123456789']);
	}

	public function testActivateThrowsWhenCardIsNotUserCard() {
		$activationModel = $this->createStub(ActivationModel::class);

		$cardModel = $this->createStub(CardModel::class);
		$cardModel->method('read')->willReturn(new ShutdownCard());

		$chargeModel = $this->createStub(ChargeModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);
		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new EquipmentService(
			$activationModel,
			$cardModel,
			$chargeModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel,
			$loggedEventModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_ACTIVATION_NOT_AUTHORIZED);
		$service->activate('00112233445566', ['HTTP_AUTHORIZATION' => 'Bearer 123456789']);
	}

	public function testActivateThrowsWhenEquipmentIsNotFound() {
		$activationModel = $this->createStub(ActivationModel::class);

		$cardModel = $this->createStub(CardModel::class);
		$cardModel->method('read')->willReturn(new UserCard());

		$chargeModel = $this->createStub(ChargeModel::class);

		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentModel->method('search')->willReturn([]);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);
		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new EquipmentService(
			$activationModel,
			$cardModel,
			$chargeModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel,
			$loggedEventModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_ACTIVATION_NOT_AUTHORIZED);
		$service->activate('00112233445566', ['HTTP_AUTHORIZATION' => 'Bearer 123456789']);
	}

	public function testActivateThrowsWhenUserNotAuthorized() {
		$mac = '00112233445566';
		$equipment_type_id = 12;
		$card_id = 123456789;
		$equipment_id = 23;

		$activationModel = $this->createStub(ActivationModel::class);

		$cardModel = $this->createStub(CardModel::class);
		$cardModel->method('read')->willReturn(
			(new UserCard())->set_user((new User())->set_id(1))
		);

		$chargeModel = $this->createStub(ChargeModel::class);

		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentModel->expects($this->once())->method('search')->with(
			$this->callback(
				fn(EquipmentQuery $query) =>
					$query->exclude_out_of_service() === true
					&& $query->mac_address() === $mac
			)
		)->willReturn([
			(new Equipment())
				->set_id($equipment_id)
				->set_type_id($equipment_type_id)
		]);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);

		$loggedEventModel = $this->createStub(LoggedEventModel::class);
		$loggedEventModel->expects($this->once())->method('create')->with(
			$this->callback(
				fn(LoggedEvent $event) =>
					$event->type_id() === LoggedEventType::UNSUCCESSFUL_AUTHENTICATION
					&& $event->card_id() === $card_id
					&& $event->equipment_id() === $equipment_id
			)
		)
		->willReturnArgument(0);

		$service = new EquipmentService(
			$activationModel,
			$cardModel,
			$chargeModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel,
			$loggedEventModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_ACTIVATION_NOT_AUTHORIZED);
		$service->activate($mac, ['HTTP_AUTHORIZATION' => "Bearer $card_id"]);
	}

	public function testActivateSucceedsWhenUserIsAuthorized() {
		$mac = '00112233445566';
		$equipment_type_id = 12;
		$card_id = 123456789;
		$equipment_id = 23;

		$authorized_user = (new User())
			->set_id(1)
			->set_authorizations([$equipment_type_id]);

		$equipment = (new Equipment())
			->set_id($equipment_id)
			->set_type_id($equipment_type_id);

		$connection = $this->createStub(PDO::class);
		$connection->expects($this->once())->method('beginTransaction');
		$connection->expects($this->once())->method('commit');

		$config = $this->createStub(Config::class);
		$config->method('writable_db_connection')->willReturn($connection);

		$activationModel = $this->createStub(ActivationModel::class);
		$activationModel->method('configuration')->willReturn($config);
		$activationModel->expects($this->once())->method('create')->with(
			$this->equalTo($equipment_id )
		);

		$cardModel = $this->createStub(CardModel::class);
		$cardModel->method('read')->willReturn(
			(new UserCard())
				->set_user($authorized_user)
		);

		$chargeModel = $this->createStub(ChargeModel::class);

		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentModel->expects($this->once())->method('search')->with(
			$this->callback(
				fn(EquipmentQuery $query) =>
					$query->exclude_out_of_service() === true
					&& $query->mac_address() === $mac
			)
		)->willReturn([$equipment]);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);

		$loggedEventModel = $this->createStub(LoggedEventModel::class);
		$loggedEventModel->expects($this->once())->method('create')->with(
			$this->callback(
				fn(LoggedEvent $event) =>
					$event->type_id() === LoggedEventType::SUCCESSFUL_AUTHENTICATION
					&& $event->card_id() === $card_id
					&& $event->equipment_id() === $equipment_id
			)
		)
		->willReturnArgument(0);

		$service = new EquipmentService(
			$activationModel,
			$cardModel,
			$chargeModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel,
			$loggedEventModel
		);

		$result = $service->activate($mac, ['HTTP_AUTHORIZATION' => "Bearer $card_id"]);
		
		self::assertIsArray($result);
		self::assertArrayHasKey('equipment', $result);
		self::assertSame($equipment, $result['equipment']);
		self::assertArrayHasKey('user', $result);
		self::assertSame($authorized_user, $result['user']);
	}

	#endregion test activate()

	#region test deactivate()

	public function testDeactivateThrowsWhenNoAuthorizationHeader() {
		$activationModel = $this->createStub(ActivationModel::class);
		$cardModel = $this->createStub(CardModel::class);
		$chargeModel = $this->createStub(ChargeModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);
		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new EquipmentService(
			$activationModel,
			$cardModel,
			$chargeModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel,
			$loggedEventModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_NO_AUTHORIZATION_HEADER);
		$service->deactivate('00112233445566', []);
	}

	public function testDeactivateThrowsWhenAuthorizationHeaderDoesNotStartWithBearer() {
		$activationModel = $this->createStub(ActivationModel::class);
		$cardModel = $this->createStub(CardModel::class);
		$chargeModel = $this->createStub(ChargeModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);
		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new EquipmentService(
			$activationModel,
			$cardModel,
			$chargeModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel,
			$loggedEventModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_INVALID_AUTHORIZATION_HEADER);
		$service->deactivate('00112233445566', ['HTTP_AUTHORIZATION' => 'let me in']);
	}

	public function testDeactivateThrowsWhenBearerTokenIsInvalid() {
		$activationModel = $this->createStub(ActivationModel::class);
		$cardModel = $this->createStub(CardModel::class);
		$chargeModel = $this->createStub(ChargeModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);
		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new EquipmentService(
			$activationModel,
			$cardModel,
			$chargeModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel,
			$loggedEventModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_INVALID_AUTHORIZATION_HEADER);
		$service->deactivate('00112233445566', ['HTTP_AUTHORIZATION' => 'Bearer let me in']);
	}

	public function testDeactivateThrowsWhenCardDoesNotExist() {
		$activationModel = $this->createStub(ActivationModel::class);

		$cardModel = $this->createStub(CardModel::class);
		$cardModel->method('read')->willReturn(null);

		$chargeModel = $this->createStub(ChargeModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);
		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new EquipmentService(
			$activationModel,
			$cardModel,
			$chargeModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel,
			$loggedEventModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_DEACTIVATION_NOT_AUTHORIZED);
		$service->deactivate('00112233445566', ['HTTP_AUTHORIZATION' => 'Bearer 123456789']);
	}

	public function testDeactivateThrowsWhenCardIsNotUserCard() {
		$activationModel = $this->createStub(ActivationModel::class);

		$cardModel = $this->createStub(CardModel::class);
		$cardModel->method('read')->willReturn(new ShutdownCard());

		$chargeModel = $this->createStub(ChargeModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);
		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new EquipmentService(
			$activationModel,
			$cardModel,
			$chargeModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel,
			$loggedEventModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_DEACTIVATION_NOT_AUTHORIZED);
		$service->deactivate('00112233445566', ['HTTP_AUTHORIZATION' => 'Bearer 123456789']);
	}

	public function testDeactivateThrowsWhenEquipmentIsNotFound() {
		$activationModel = $this->createStub(ActivationModel::class);

		$cardModel = $this->createStub(CardModel::class);
		$cardModel->method('read')->willReturn(new UserCard());

		$chargeModel = $this->createStub(ChargeModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentModel->method('search')->willReturn([]);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);
		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new EquipmentService(
			$activationModel,
			$cardModel,
			$chargeModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel,
			$loggedEventModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_DEACTIVATION_NOT_AUTHORIZED);
		$service->deactivate('00112233445566', ['HTTP_AUTHORIZATION' => 'Bearer 123456789']);
	}

	public function testDeactivateSuccessWithNoCharge() {
		$mac = '00112233445566';
		$equipment_type_id = 12;
		$card_id = 123456789;
		$equipment_id = 23;
		$service_minutes = 123;
		$duration = 2;

		$authorized_user = (new User())
			->set_id(1)
			->set_authorizations([$equipment_type_id]);

		$equipment = (new Equipment())
			->set_id($equipment_id)
			->set_service_minutes($service_minutes)
			->set_type(
				(new EquipmentType())
					->set_id($equipment_type_id)
					->set_charge_policy_id(ChargePolicy::NO_CHARGE)
			);

		$connection = $this->createStub(PDO::class);
		$connection->expects($this->once())->method('beginTransaction');
		$connection->expects($this->once())->method('commit');

		$config = $this->createStub(Config::class);
		$config->method('writable_db_connection')->willReturn($connection);

		$activationModel = $this->createStub(ActivationModel::class);
		$activationModel->method('configuration')->willReturn($config);
		$activationModel->expects($this->once())->method('delete')->with(
			$this->equalTo($equipment_id )
		)->willReturn(
			(new DateTimeImmutable())->sub(new DateInterval('PT' . $duration . 'M'))
		);

		$cardModel = $this->createStub(CardModel::class);
		$cardModel->method('read')->willReturn(
			(new UserCard())
				->set_user($authorized_user)
		);

		$chargeModel = $this->createStub(ChargeModel::class);

		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentModel->expects($this->once())->method('search')->with(
			$this->callback(
				fn(EquipmentQuery $query) =>
					$query->exclude_out_of_service() === true
					&& $query->mac_address() === $mac
			)
		)->willReturn([$equipment]);
		$equipmentModel->expects($this->once())->method('update')->with(
			$this->callback(
				fn (Equipment $equipment) =>
					$equipment->service_minutes() === $service_minutes + $duration
			)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);

		$loggedEventModel = $this->createStub(LoggedEventModel::class);
		$loggedEventModel->expects($this->once())->method('create')->with(
			$this->callback(
				fn(LoggedEvent $event) =>
					$event->type_id() === LoggedEventType::DEAUTHENTICATION
					&& $event->card_id() === $card_id
					&& $event->equipment_id() === $equipment_id
			)
		)
		->willReturnArgument(0);

		$service = new EquipmentService(
			$activationModel,
			$cardModel,
			$chargeModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel,
			$loggedEventModel
		);

		self::assertSame(
			$equipment,
			$service->deactivate($mac, ['HTTP_AUTHORIZATION' => "Bearer $card_id"])
		);
	}

	public function testDeactivateSuccessWithChargePerUse() {
		$mac = '00112233445566';
		$equipment_type_id = 12;
		$card_id = 123456789;
		$equipment_id = 23;
		$service_minutes = 123;
		$duration = 1;
		$rate = '1.75';
		$user_id = 12;

		$authorized_user = (new User())
			->set_id($user_id)
			->set_authorizations([$equipment_type_id]);

		$equipment = (new Equipment())
			->set_id($equipment_id)
			->set_service_minutes($service_minutes)
			->set_type(
				(new EquipmentType())
					->set_id($equipment_type_id)
					->set_charge_policy_id(ChargePolicy::PER_USE)
					->set_charge_rate($rate)
			);

		$connection = $this->createStub(PDO::class);
		$connection->expects($this->once())->method('beginTransaction');
		$connection->expects($this->once())->method('commit');

		$config = $this->createStub(Config::class);
		$config->method('writable_db_connection')->willReturn($connection);

		$activationModel = $this->createStub(ActivationModel::class);
		$activationModel->method('configuration')->willReturn($config);
		$activationModel->expects($this->once())->method('delete')->with(
			$this->equalTo($equipment_id )
		)->willReturn(
			(new DateTimeImmutable())->sub(new DateInterval('PT' . $duration . 'S'))
		);

		$cardModel = $this->createStub(CardModel::class);
		$cardModel->method('read')->willReturn(
			(new UserCard())
				->set_user($authorized_user)
		);

		$chargeModel = $this->createStub(ChargeModel::class);
		$chargeModel->expects($this->once())->method('create')->with(
			$this->callback(
				fn (Charge $charge) =>
					$charge->equipment_id() === $equipment_id
					&& $charge->user_id() === $user_id
					&& $charge->amount() === $rate
					&& $charge->charge_policy_id() === ChargePolicy::PER_USE
					&& $charge->charge_rate() === $rate
					&& $charge->charged_time() === $duration
			)
		);

		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentModel->expects($this->once())->method('search')->with(
			$this->callback(
				fn (EquipmentQuery $query) =>
					$query->exclude_out_of_service() === true
					&& $query->mac_address() === $mac
			)
		)->willReturn([$equipment]);
		$equipmentModel->expects($this->once())->method('update')->with(
			$this->callback(
				fn (Equipment $equipment) =>
					$equipment->service_minutes() === $service_minutes + $duration
			)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);

		$loggedEventModel = $this->createStub(LoggedEventModel::class);
		$loggedEventModel->expects($this->once())->method('create')->with(
			$this->callback(
				fn(LoggedEvent $event) =>
					$event->type_id() === LoggedEventType::DEAUTHENTICATION
					&& $event->card_id() === $card_id
					&& $event->equipment_id() === $equipment_id
			)
		)
		->willReturnArgument(0);

		$service = new EquipmentService(
			$activationModel,
			$cardModel,
			$chargeModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel,
			$loggedEventModel
		);

		self::assertSame(
			$equipment,
			$service->deactivate($mac, ['HTTP_AUTHORIZATION' => "Bearer $card_id"])
		);
	}

	public function testDeactivateSuccessWithChargePerMinute() {
		$mac = '00112233445566';
		$equipment_type_id = 12;
		$card_id = 123456789;
		$equipment_id = 23;
		$service_minutes = 123;
		$duration = 1;
		$rate = '1.75';
		$user_id = 12;

		$authorized_user = (new User())
			->set_id($user_id)
			->set_authorizations([$equipment_type_id]);

		$equipment = (new Equipment())
			->set_id($equipment_id)
			->set_service_minutes($service_minutes)
			->set_type(
				(new EquipmentType())
					->set_id($equipment_type_id)
					->set_charge_policy_id(ChargePolicy::PER_MINUTE)
					->set_charge_rate($rate)
			);

		$connection = $this->createStub(PDO::class);
		$connection->expects($this->once())->method('beginTransaction');
		$connection->expects($this->once())->method('commit');

		$config = $this->createStub(Config::class);
		$config->method('writable_db_connection')->willReturn($connection);

		$activationModel = $this->createStub(ActivationModel::class);
		$activationModel->method('configuration')->willReturn($config);
		$activationModel->expects($this->once())->method('delete')->with(
			$this->equalTo($equipment_id )
		)->willReturn(
			(new DateTimeImmutable())->sub(new DateInterval('PT' . $duration . 'S'))
		);

		$cardModel = $this->createStub(CardModel::class);
		$cardModel->method('read')->willReturn(
			(new UserCard())
				->set_user($authorized_user)
		);

		$chargeModel = $this->createStub(ChargeModel::class);
		$chargeModel->expects($this->once())->method('create')->with(
			$this->callback(
				fn (Charge $charge) =>
					$charge->equipment_id() === $equipment_id
					&& $charge->user_id() === $user_id
					&& $charge->amount() === $rate
					&& $charge->charge_policy_id() === ChargePolicy::PER_MINUTE
					&& $charge->charge_rate() === $rate
					&& $charge->charged_time() === $duration
			)
		);

		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentModel->expects($this->once())->method('search')->with(
			$this->callback(
				fn (EquipmentQuery $query) =>
					$query->exclude_out_of_service() === true
					&& $query->mac_address() === $mac
			)
		)->willReturn([$equipment]);
		$equipmentModel->expects($this->once())->method('update')->with(
			$this->callback(
				fn (Equipment $equipment) =>
					$equipment->service_minutes() === $service_minutes + $duration
			)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);

		$loggedEventModel = $this->createStub(LoggedEventModel::class);
		$loggedEventModel->expects($this->once())->method('create')->with(
			$this->callback(
				fn(LoggedEvent $event) =>
					$event->type_id() === LoggedEventType::DEAUTHENTICATION
					&& $event->card_id() === $card_id
					&& $event->equipment_id() === $equipment_id
			)
		)
		->willReturnArgument(0);

		$service = new EquipmentService(
			$activationModel,
			$cardModel,
			$chargeModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel,
			$loggedEventModel
		);

		self::assertSame(
			$equipment,
			$service->deactivate($mac, ['HTTP_AUTHORIZATION' => "Bearer $card_id"])
		);
	}

	#endregion deactivate

	#region test changeStatus()

	public function testChangeStatusThrowsWhenFileIsNotReadable() {
		$activationModel = $this->createStub(ActivationModel::class);
		$cardModel = $this->createStub(CardModel::class);
		$chargeModel = $this->createStub(ChargeModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);
		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new EquipmentService(
			$activationModel,
			$cardModel,
			$chargeModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel,
			$loggedEventModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_INVALID_STATUS_CHANGE_BODY);
		// PHP warning is intentionally suppressed in next line for testing
		@$service->changeStatus(
			'file_does_not_exist.txt',
			'00112233445566',
			[]
		);
	}

	public function testChangeStatusThrowsWhenFileDataIsNotARecognizedStatusChange() {
		$activationModel = $this->createStub(ActivationModel::class);
		$cardModel = $this->createStub(CardModel::class);
		$chargeModel = $this->createStub(ChargeModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);
		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new EquipmentService(
			$activationModel,
			$cardModel,
			$chargeModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel,
			$loggedEventModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_INVALID_STATUS_CHANGE_BODY);
		$service->changeStatus(
			realpath(__DIR__ . '/EquipmentServiceTestData/InvalidStatusChange.txt'),
			'00112233445566',
			[]
		);
	}

	#endregion test changeStatus()

	#region test changeStatus(shutdown)

	public function testShutdownThrowsWhenNoAuthorizationHeader() {
		$activationModel = $this->createStub(ActivationModel::class);
		$cardModel = $this->createStub(CardModel::class);
		$chargeModel = $this->createStub(ChargeModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);
		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new EquipmentService(
			$activationModel,
			$cardModel,
			$chargeModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel,
			$loggedEventModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_NO_AUTHORIZATION_HEADER);
		$service->changeStatus(
			realpath(__DIR__ . '/EquipmentServiceTestData/ShutdownStatusChange.txt'),
			'00112233445566',
			[]
		);
	}

	public function testShutdownThrowsWhenAuthorizationHeaderDoesNotStartWithBearer() {
		$activationModel = $this->createStub(ActivationModel::class);
		$cardModel = $this->createStub(CardModel::class);
		$chargeModel = $this->createStub(ChargeModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);
		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new EquipmentService(
			$activationModel,
			$cardModel,
			$chargeModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel,
			$loggedEventModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_INVALID_AUTHORIZATION_HEADER);
		$service->changeStatus(
			realpath(__DIR__ . '/EquipmentServiceTestData/ShutdownStatusChange.txt'),
			'00112233445566',
			['HTTP_AUTHORIZATION' => 'let me in']
		);
	}

	public function testShutdownThrowsWhenBearerTokenIsInvalid() {
		$activationModel = $this->createStub(ActivationModel::class);
		$cardModel = $this->createStub(CardModel::class);
		$chargeModel = $this->createStub(ChargeModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);
		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new EquipmentService(
			$activationModel,
			$cardModel,
			$chargeModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel,
			$loggedEventModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_INVALID_AUTHORIZATION_HEADER);
		$service->changeStatus(
			realpath(__DIR__ . '/EquipmentServiceTestData/ShutdownStatusChange.txt'),
			'00112233445566',
			['HTTP_AUTHORIZATION' => 'Bearer let me in']
		);
	}

	public function testShutdownThrowsWhenCardDoesNotExist() {
		$activationModel = $this->createStub(ActivationModel::class);

		$cardModel = $this->createStub(CardModel::class);
		$cardModel->method('read')->willReturn(null);

		$chargeModel = $this->createStub(ChargeModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);
		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new EquipmentService(
			$activationModel,
			$cardModel,
			$chargeModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel,
			$loggedEventModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_SHUTDOWN_NOT_AUTHORIZED);
		$service->changeStatus(
			realpath(__DIR__ . '/EquipmentServiceTestData/ShutdownStatusChange.txt'),
			'00112233445566',
			['HTTP_AUTHORIZATION' => 'Bearer 123456789']
		);
	}

	public function testShutdownThrowsWhenCardIsNotShutdownCard() {
		$activationModel = $this->createStub(ActivationModel::class);

		$cardModel = $this->createStub(CardModel::class);
		$cardModel->method('read')->willReturn(new UserCard());

		$chargeModel = $this->createStub(ChargeModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);
		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new EquipmentService(
			$activationModel,
			$cardModel,
			$chargeModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel,
			$loggedEventModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_SHUTDOWN_NOT_AUTHORIZED);
		$service->changeStatus(
			realpath(__DIR__ . '/EquipmentServiceTestData/ShutdownStatusChange.txt'),
			'00112233445566',
			['HTTP_AUTHORIZATION' => 'Bearer 123456789']
		);
	}

	public function testShutdownThrowsWhenEquipmentIsNotFound() {
		$activationModel = $this->createStub(ActivationModel::class);

		$cardModel = $this->createStub(CardModel::class);
		$cardModel->method('read')->willReturn(new ShutdownCard());

		$chargeModel = $this->createStub(ChargeModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentModel->method('search')->willReturn([]);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);
		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new EquipmentService(
			$activationModel,
			$cardModel,
			$chargeModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel,
			$loggedEventModel
		);

		self::expectException(NotFoundException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_EQUIPMENT_NOT_FOUND);
		$service->changeStatus(
			realpath(__DIR__ . '/EquipmentServiceTestData/ShutdownStatusChange.txt'),
			'00112233445566',
			['HTTP_AUTHORIZATION' => 'Bearer 123456789']
		);
	}

	public function testShutdownSuccess() {
		$mac = '00112233445566';
		$card_id = 123456789;
		$equipment_id = 23;

		$equipment = (new Equipment())->set_id($equipment_id);

		$activationModel = $this->createStub(ActivationModel::class);

		$cardModel = $this->createStub(CardModel::class);
		$cardModel->method('read')->willReturn(new ShutdownCard());

		$chargeModel = $this->createStub(ChargeModel::class);

		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentModel->expects($this->once())->method('search')->with(
			$this->callback(
				fn(EquipmentQuery $query) =>
					$query->exclude_out_of_service() === true
					&& $query->mac_address() === $mac
			)
		)->willReturn([$equipment]);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);

		$loggedEventModel = $this->createStub(LoggedEventModel::class);
		$loggedEventModel->expects($this->once())->method('create')->with(
			$this->callback(
				fn(LoggedEvent $event) =>
					$event->type_id() === LoggedEventType::PLANNED_SHUTDOWN
					&& $event->card_id() === $card_id
					&& $event->equipment_id() === $equipment_id
			)
		)
		->willReturnArgument(0);

		$service = new EquipmentService(
			$activationModel,
			$cardModel,
			$chargeModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel,
			$loggedEventModel
		);

		self::assertSame($equipment, $service->changeStatus(
			realpath(__DIR__ . '/EquipmentServiceTestData/ShutdownStatusChange.txt'),
			$mac,
			['HTTP_AUTHORIZATION' => "Bearer $card_id"]
		));
	}

	#endregion test changeStatus(shutdown)

	#region test changeStatus(startup)

	public function testStartupThrowsWhenEquipmentIsNotFound() {
		$activationModel = $this->createStub(ActivationModel::class);
		$cardModel = $this->createStub(CardModel::class);
		$chargeModel = $this->createStub(ChargeModel::class);

		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentModel->method('search')->willReturn([]);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);
		$loggedEventModel = $this->createStub(LoggedEventModel::class);

		$service = new EquipmentService(
			$activationModel,
			$cardModel,
			$chargeModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel,
			$loggedEventModel
		);

		self::expectException(NotFoundException::class);
		self::expectExceptionMessage(EquipmentService::ERROR_EQUIPMENT_NOT_FOUND);
		$service->changeStatus(
			realpath(__DIR__ . '/EquipmentServiceTestData/StartupStatusChange.txt'),
			'00112233445566',
			[]
		);
	}

	public function testStartupSuccess() {
		$mac = '00112233445566';
		$equipment_id = 23;

		$equipment = (new Equipment())->set_id($equipment_id);

		$activationModel = $this->createStub(ActivationModel::class);
		$cardModel = $this->createStub(CardModel::class);

		$chargeModel = $this->createStub(ChargeModel::class);

		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentModel->expects($this->once())->method('search')->with(
			$this->callback(
				fn(EquipmentQuery $query) =>
					$query->exclude_out_of_service() === true
					&& $query->mac_address() === $mac
			)
		)->willReturn([$equipment]);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$locationModel = $this->createStub(LocationModel::class);

		$loggedEventModel = $this->createStub(LoggedEventModel::class);
		$loggedEventModel->expects($this->once())->method('create')->with(
			$this->callback(
				fn(LoggedEvent $event) =>
					$event->type_id() === LoggedEventType::STARTUP_COMPLETE
					&& $event->card_id() === null
					&& $event->equipment_id() === $equipment_id
			)
		)
		->willReturnArgument(0);

		$service = new EquipmentService(
			$activationModel,
			$cardModel,
			$chargeModel,
			$equipmentModel,
			$equipmentTypeModel,
			$locationModel,
			$loggedEventModel
		);

		self::assertSame($equipment, $service->changeStatus(
			realpath(__DIR__ . '/EquipmentServiceTestData/StartupStatusChange.txt'),
			$mac,
			[]
		));
	}

	#endregion test changeStatus(startup)
}
