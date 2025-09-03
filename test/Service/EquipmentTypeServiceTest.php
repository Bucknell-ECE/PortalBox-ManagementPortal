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
use Portalbox\Exception\NotFoundException;
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
		$service->create(realpath(__DIR__ . '/EquipmentTypeServiceTestData/DataIsNotArray.json'));
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
		$service->create(realpath(__DIR__ . '/EquipmentTypeServiceTestData/NameIsNotSpecified.json'));
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
		$service->create(realpath(__DIR__ . '/EquipmentTypeServiceTestData/NameIsInvalid.json'));
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
		$service->create(realpath(__DIR__ . '/EquipmentTypeServiceTestData/RequiresTrainingIsInvalid.json'));
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
		$service->create(realpath(__DIR__ . '/EquipmentTypeServiceTestData/ChargePolicyIsInvalid.json'));
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
		$service->create(realpath(__DIR__ . '/EquipmentTypeServiceTestData/ChargeRateIsInvalid.json'));
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
		$service->create(realpath(__DIR__ . '/EquipmentTypeServiceTestData/AllowProxyIsInvalid.json'));
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

		$equipmentType = $service->create(realpath(__DIR__ . '/EquipmentTypeServiceTestData/HappyPathData.json'));

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

		$equipmentType = $service->create(realpath(__DIR__ . '/EquipmentTypeServiceTestData/ValidDataNeedingSanitized.json'));

		self::assertInstanceOf(EquipmentType::class, $equipmentType);
		self::assertSame('Flashlight', $equipmentType->name());
		self::assertSame(true, $equipmentType->requires_training());
		self::assertSame('10', $equipmentType->charge_rate());
		self::assertSame(ChargePolicy::PER_MINUTE, $equipmentType->charge_policy_id());
		self::assertSame(false, $equipmentType->allow_proxy());
	}

	#endregion test create()
	
	#region test readAll()

	public function testReadThrowsWhenNotAuthenticated() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new EquipmentTypeService(
			$session,
			$equipmentTypeModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(EquipmentTypeService::ERROR_UNAUTHENTICATED_READ);
		$service->read(1);
	}

	public function testReadThrowsWhenNotAuthorized() {
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
		self::expectExceptionMessage(EquipmentTypeService::ERROR_UNAUTHORIZED_READ);
		$service->read(1);
	}

	public function testReadThrowsWhenNotFound() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::READ_EQUIPMENT_TYPE])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('read')->willReturn(null);

		$service = new EquipmentTypeService(
			$session,
			$equipmentTypeModel
		);

		self::expectException(NotFoundException::class);
		self::expectExceptionMessage(EquipmentTypeService::ERROR_EQUIPMENT_TYPE_NOT_FOUND);
		$service->read(1);
	}

	public function testReadSuccess() {
		$type = (new EquipmentType())->set_id(1);

		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::READ_EQUIPMENT_TYPE])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('read')->willReturn($type);

		$service = new EquipmentTypeService(
			$session,
			$equipmentTypeModel
		);

		self::assertSame($type, $service->read(1));
	}

	#endregion test readAll()

	#region test readAll()

	public function testReadAllThrowsWhenNotAuthenticated() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new EquipmentTypeService(
			$session,
			$equipmentTypeModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(EquipmentTypeService::ERROR_UNAUTHENTICATED_READ);
		$service->readAll();
	}

	public function testReadAllThrowsWhenNotAuthorized() {
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
		self::expectExceptionMessage(EquipmentTypeService::ERROR_UNAUTHORIZED_READ);
		$service->readAll();
	}

	public function testReadAllSuccess() {
		$types = [
			(new EquipmentType())->set_id(1),
			(new EquipmentType())->set_id(2)
		];

		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::LIST_EQUIPMENT_TYPES])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('search')->willReturn($types);

		$service = new EquipmentTypeService(
			$session,
			$equipmentTypeModel
		);

		self::assertSame($types, $service->readAll());
	}

	#endregion test readAll()

	#region test update()

	public function testUpdateThrowsWhenNotAuthenticated() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new EquipmentTypeService(
			$session,
			$equipmentTypeModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(EquipmentTypeService::ERROR_UNAUTHENTICATED_MODIFY);
		$service->update(1, 'not a file path');
	}

	public function testUpdateThrowsWhenNotAuthorized() {
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
		self::expectExceptionMessage(EquipmentTypeService::ERROR_UNAUTHORIZED_MODIFY);
		$service->update(1, 'not a file path');
	}

	public function testUpdateThrowsWhenEquipmentTypeNotFound() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_EQUIPMENT_TYPE])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('read')->willReturn(null);

		$service = new EquipmentTypeService(
			$session,
			$equipmentTypeModel
		);

		self::expectException(NotFoundException::class);
		self::expectExceptionMessage(EquipmentTypeService::ERROR_EQUIPMENT_TYPE_NOT_FOUND);
		$service->update(1, 'not a file path');
	}

	public function testUpdateThrowsWhenFileIsNotReadable() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_EQUIPMENT_TYPE])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('read')->willReturn(new EquipmentType());

		$service = new EquipmentTypeService(
			$session,
			$equipmentTypeModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentTypeService::ERROR_INVALID_EQUIPMENT_TYPE_DATA);
		// PHP warning is intentionally suppressed in next line for testing
		@$service->update(1, 'file_does_not_exist.json');
	}

	public function testUpdateThrowsWhenDataIsNotArray() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_EQUIPMENT_TYPE])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('read')->willReturn(new EquipmentType());

		$service = new EquipmentTypeService(
			$session,
			$equipmentTypeModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentTypeService::ERROR_INVALID_EQUIPMENT_TYPE_DATA);
		$service->update(1, realpath(__DIR__ . '/EquipmentTypeServiceTestData/DataIsNotArray.json'));
	}

	public function testUpdateThrowsWhenNameIsNotSpecified() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_EQUIPMENT_TYPE])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('read')->willReturn(new EquipmentType());

		$service = new EquipmentTypeService(
			$session,
			$equipmentTypeModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentTypeService::ERROR_NAME_IS_REQUIRED);
		$service->update(1, realpath(__DIR__ . '/EquipmentTypeServiceTestData/NameIsNotSpecified.json'));
	}

	public function testUpdateThrowsWhenNameIsInvalid() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_EQUIPMENT_TYPE])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('read')->willReturn(new EquipmentType());

		$service = new EquipmentTypeService(
			$session,
			$equipmentTypeModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentTypeService::ERROR_NAME_IS_REQUIRED);
		$service->update(1, realpath(__DIR__ . '/EquipmentTypeServiceTestData/NameIsInvalid.json'));
	}

	public function testUpdateThrowsWhenRequiresTrainingIsInvalid() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_EQUIPMENT_TYPE])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('read')->willReturn(new EquipmentType());

		$service = new EquipmentTypeService(
			$session,
			$equipmentTypeModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentTypeService::ERROR_REQUIRES_TRAINING_IS_REQUIRED);
		$service->update(1, realpath(__DIR__ . '/EquipmentTypeServiceTestData/RequiresTrainingIsInvalid.json'));
	}

	public function testUpdateThrowsWhenChargePolicyIsInvalid() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_EQUIPMENT_TYPE])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('read')->willReturn(new EquipmentType());

		$service = new EquipmentTypeService(
			$session,
			$equipmentTypeModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentTypeService::ERROR_INVALID_CHARGE_POLICY);
		$service->update(1, realpath(__DIR__ . '/EquipmentTypeServiceTestData/ChargePolicyIsInvalid.json'));
	}

	public function testUpdateThrowsWhenChargeRateIsInvalid() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_EQUIPMENT_TYPE])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('read')->willReturn(new EquipmentType());

		$service = new EquipmentTypeService(
			$session,
			$equipmentTypeModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentTypeService::ERROR_INVALID_RATE);
		$service->update(1, realpath(__DIR__ . '/EquipmentTypeServiceTestData/ChargeRateIsInvalid.json'));
	}

	public function testUpdateThrowsWhenAllowProxyIsInvalid() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_EQUIPMENT_TYPE])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('read')->willReturn(new EquipmentType());

		$service = new EquipmentTypeService(
			$session,
			$equipmentTypeModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentTypeService::ERROR_ALLOWS_PROXY_IS_REQUIRED);
		$service->update(1, realpath(__DIR__ . '/EquipmentTypeServiceTestData/AllowProxyIsInvalid.json'));
	}

	public function testUpdateSuccess() {
		$id = 34;

		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_EQUIPMENT_TYPE])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('read')->willReturn(new EquipmentType());
		$equipmentTypeModel->expects($this->once())->method('update')->willReturnArgument(0);

		$service = new EquipmentTypeService(
			$session,
			$equipmentTypeModel
		);

		$equipmentType = $service->update($id, realpath(__DIR__ . '/EquipmentTypeServiceTestData/HappyPathData.json'));

		self::assertInstanceOf(EquipmentType::class, $equipmentType);
		self::assertSame($id, $equipmentType->id());
		self::assertSame('Flashlight', $equipmentType->name());
		self::assertSame(false, $equipmentType->requires_training());
		self::assertSame('0.01', $equipmentType->charge_rate());
		self::assertSame(ChargePolicy::NO_CHARGE, $equipmentType->charge_policy_id());
		self::assertSame(true, $equipmentType->allow_proxy());
	}

	public function testUpdateSuccessSanitizesData() {
		$id = 34;

		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_EQUIPMENT_TYPE])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('read')->willReturn(new EquipmentType());
		$equipmentTypeModel->expects($this->once())->method('update')->willReturnArgument(0);

		$service = new EquipmentTypeService(
			$session,
			$equipmentTypeModel
		);

		$equipmentType = $service->update($id, realpath(__DIR__ . '/EquipmentTypeServiceTestData/ValidDataNeedingSanitized.json'));

		self::assertInstanceOf(EquipmentType::class, $equipmentType);
		self::assertSame($id, $equipmentType->id());
		self::assertSame('Flashlight', $equipmentType->name());
		self::assertSame(true, $equipmentType->requires_training());
		self::assertSame('10', $equipmentType->charge_rate());
		self::assertSame(ChargePolicy::PER_MINUTE, $equipmentType->charge_policy_id());
		self::assertSame(false, $equipmentType->allow_proxy());
	}

	#endregion test update()
}
