<?php

declare(strict_types=1);

namespace Test\Portalbox\Service;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Portalbox\Entity\ChargePolicy;
use Portalbox\Entity\EquipmentType;
use Portalbox\Entity\Permission;
use Portalbox\Entity\Role;
use Portalbox\Entity\User;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Service\EquipmentTypeService;
use Portalbox\Session\SessionInterface;

final class EquipmentTypeServiceTest extends TestCase {
	#region test create()

	public function testCreateThrowsWhenNotAuthenticated() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new EquipmentTypeService(
			$session,
			$equipmentTypeModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(EquipmentTypeService::ERROR_UNAUTHENTICATED_CREATE);
		$service->create('not a file path');
	}

	public function testCreateThrowsWhenNotAuthorized() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new EquipmentTypeService(
			$session,
			$equipmentTypeModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(EquipmentTypeService::ERROR_UNAUTHORIZED_CREATE);
		$service->create('not a file path');
	}

	public function testCreateThrowsWhenFileIsNotReadable() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_EQUIPMENT_TYPE])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new EquipmentTypeService(
			$session,
			$equipmentTypeModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentTypeService::ERROR_INVALID_EQUIPMENT_TYPE_DATA);
		// PHP warning is intentionally suppressed in next line for testing
		@$service->create('file_does_not_exist.json');
	}

	public function testCreateThrowsWhenDataIsNotArray() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_EQUIPMENT_TYPE])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new EquipmentTypeService(
			$session,
			$equipmentTypeModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentTypeService::ERROR_INVALID_EQUIPMENT_TYPE_DATA);
		$service->create(realpath(__DIR__ . '/EquipmentTypeServiceTestData/CreateThrowsWhenDataIsNotArray.json'));
	}

	public function testCreateThrowsWhenNameIsNotSpecified() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_EQUIPMENT_TYPE])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new EquipmentTypeService(
			$session,
			$equipmentTypeModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentTypeService::ERROR_NAME_IS_REQUIRED);
		$service->create(realpath(__DIR__ . '/EquipmentTypeServiceTestData/CreateThrowsWhenNameIsNotSpecified.json'));
	}

	public function testCreateThrowsWhenNameIsInvalid() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_EQUIPMENT_TYPE])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new EquipmentTypeService(
			$session,
			$equipmentTypeModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentTypeService::ERROR_NAME_IS_REQUIRED);
		$service->create(realpath(__DIR__ . '/EquipmentTypeServiceTestData/CreateThrowsWhenNameIsInvalid.json'));
	}

	public function testCreateThrowsWhenRequiresTrainingIsInvalid() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_EQUIPMENT_TYPE])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new EquipmentTypeService(
			$session,
			$equipmentTypeModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentTypeService::ERROR_REQUIRES_TRAINING_IS_REQUIRED);
		$service->create(realpath(__DIR__ . '/EquipmentTypeServiceTestData/CreateThrowsWhenRequiresTrainingIsInvalid.json'));
	}

	public function testCreateThrowsWhenChargePolicyIsInvalid() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_EQUIPMENT_TYPE])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new EquipmentTypeService(
			$session,
			$equipmentTypeModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentTypeService::ERROR_INVALID_CHARGE_POLICY);
		$service->create(realpath(__DIR__ . '/EquipmentTypeServiceTestData/CreateThrowsWhenChargePolicyIsInvalid.json'));
	}

	public function testCreateThrowsWhenChargeRateIsInvalid() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_EQUIPMENT_TYPE])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new EquipmentTypeService(
			$session,
			$equipmentTypeModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentTypeService::ERROR_INVALID_RATE);
		$service->create(realpath(__DIR__ . '/EquipmentTypeServiceTestData/CreateThrowsWhenChargeRateIsInvalid.json'));
	}

	public function testCreateThrowsWhenAllowProxyIsInvalid() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_EQUIPMENT_TYPE])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new EquipmentTypeService(
			$session,
			$equipmentTypeModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentTypeService::ERROR_ALLOWS_PROXY_IS_REQUIRED);
		$service->create(realpath(__DIR__ . '/EquipmentTypeServiceTestData/CreateThrowsWhenAllowProxyIsInvalid.json'));
	}

	public function testCreateSuccess() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_EQUIPMENT_TYPE])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->expects($this->once())->method('create')->willReturnArgument(0);

		$service = new EquipmentTypeService(
			$session,
			$equipmentTypeModel
		);

		$equipmentType = $service->create(realpath(__DIR__ . '/EquipmentTypeServiceTestData/CreateSuccess.json'));

		self::assertInstanceOf(EquipmentType::class, $equipmentType);
		self::assertSame('Flashlight', $equipmentType->name());
		self::assertSame(false, $equipmentType->requires_training());
		self::assertSame('0.01', $equipmentType->charge_rate());
		self::assertSame(ChargePolicy::NO_CHARGE, $equipmentType->charge_policy_id());
		self::assertSame(true, $equipmentType->allow_proxy());
	}

	public function testCreateSuccessSanitizesData() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_EQUIPMENT_TYPE])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->expects($this->once())->method('create')->willReturnArgument(0);

		$service = new EquipmentTypeService(
			$session,
			$equipmentTypeModel
		);

		$equipmentType = $service->create(realpath(__DIR__ . '/EquipmentTypeServiceTestData/CreateSuccessSanitizesData.json'));

		self::assertInstanceOf(EquipmentType::class, $equipmentType);
		self::assertSame('Flashlight', $equipmentType->name());
		self::assertSame(true, $equipmentType->requires_training());
		self::assertSame('10', $equipmentType->charge_rate());
		self::assertSame(ChargePolicy::PER_MINUTE, $equipmentType->charge_policy_id());
		self::assertSame(false, $equipmentType->allow_proxy());
	}

	#endregion test create()
}
