<?php

declare(strict_types=1);

namespace Test\Portalbox\Service;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Portalbox\Enumeration\Permission;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Exception\NotFoundException;
use Portalbox\Model\BadgeRuleModel;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Service\BadgeRuleService;
use Portalbox\Session;
use Portalbox\Type\BadgeRule;
use Portalbox\Type\EquipmentType;
use Portalbox\Type\Role;
use Portalbox\Type\User;

final class BadgeRuleServiceTest extends TestCase {
	#region test create()

	public function testCreateThrowsWhenNotAuthenticated() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_UNAUTHENTICATED_CREATE);
		$service->create('not a file path');
	}

	public function testCreateThrowsWhenNotAuthorized() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_UNAUTHORIZED_CREATE);
		$service->create('not a file path');
	}

	public function testCreateThrowsWhenFileIsNotReadable() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_INVALID_BADGE_RULE_DATA);
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
						->set_permissions([Permission::CREATE_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_INVALID_BADGE_RULE_DATA);
		$service->create(realpath(__DIR__ . '/BadgeRuleServiceTestData/CreateThrowsWhenDataIsNotArray.json'));
	}

	public function testCreateThrowsWhenNameIsNotSpecified() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_NAME_IS_REQUIRED);
		$service->create(realpath(__DIR__ . '/BadgeRuleServiceTestData/CreateThrowsWhenNameIsNotSpecified.json'));
	}

	public function testCreateThrowsWhenNameIsInvalid() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_NAME_IS_INVALID);
		$service->create(realpath(__DIR__ . '/BadgeRuleServiceTestData/CreateThrowsWhenNameIsInvalid.json'));
	}

	public function testCreateThrowsWhenEquipmentTypesIsWrongType() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_EQUIPMENT_TYPES_ARE_INVALID);
		$service->create(realpath(__DIR__ . '/BadgeRuleServiceTestData/CreateThrowsWhenEquipmentTypesIsWrongType.json'));
	}

	public function testCreateThrowsWhenEquipmentTypeIdIsInvalid() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_EQUIPMENT_TYPES_ARE_INVALID);
		$service->create(realpath(__DIR__ . '/BadgeRuleServiceTestData/CreateThrowsWhenEquipmentTypeIdIsInvalid.json'));
	}

	public function testCreateThrowsWhenEquipmentTypeDoesNotExist() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('search')->willReturn([]);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_EQUIPMENT_TYPES_ARE_INVALID);
		$service->create(realpath(__DIR__ . '/BadgeRuleServiceTestData/CreateThrowsWhenEquipmentTypeDoesNotExist.json'));
	}

	public function testCreateSuccessNoEquipmentTypes() {
		$name = 'Electronics Technician'; // the sanitized name from the input file

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createMock(BadgeRuleModel::class);
		$badgeRuleModel->expects($this->once())->method('create')->with(
			$this->callback(
				fn(BadgeRule $rule) => $rule->name() === $name
			)
		)
		->willReturnArgument(0);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('search')->willReturn([]);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		$rule = $service->create(realpath(__DIR__ . '/BadgeRuleServiceTestData/CreateSuccessNoEquipmentTypes.json'));

		self::assertInstanceOf(BadgeRule::class, $rule);
		self::assertSame($name, $rule->name());
	}

	public function testCreateSuccess() {
		$name = 'Electronics Technician'; // the sanitized name from the input file
		$equipment_type_id = 2;

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createMock(BadgeRuleModel::class);
		$badgeRuleModel->expects($this->once())->method('create')->with(
			$this->callback(
				fn(BadgeRule $rule) => $rule->name() === $name
			)
		)
		->willReturnArgument(0);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('search')->willReturn([
			(new EquipmentType())->set_id($equipment_type_id)
		]);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		$rule = $service->create(realpath(__DIR__ . '/BadgeRuleServiceTestData/CreateSuccess.json'));

		self::assertInstanceOf(BadgeRule::class, $rule);
		self::assertSame($name, $rule->name());
		self::assertSame([$equipment_type_id], $rule->equipment_type_ids());
	}

	#endregion test create()

	#region test read()

	public function testReadThrowsWhenNotAuthenticated() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_UNAUTHENTICATED_READ);
		$service->read(23);
	}

	public function testReadThrowsWhenNotAuthorized() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_UNAUTHORIZED_READ);
		$service->read(23);
	}

	public function testReadThrowsWhenKeyDoesNotExist() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::READ_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		self::expectException(NotFoundException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_BADGE_RULE_NOT_FOUND);
		$service->read(23);
	}

	public function testReadSuccess() {
		$rule = new BadgeRule();

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::READ_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);
		$badgeRuleModel->method('read')->willReturn($rule);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		self::assertSame($rule, $service->read(23));
	}

	#endregion test read()

	#region test readAll()

	public function testReadAllThrowsWhenNotAuthenticated() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_UNAUTHENTICATED_READ);
		$service->readAll([]);
	}

	public function testReadAllThrowsWhenNotAuthorized() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_UNAUTHORIZED_READ);
		$service->readAll([]);
	}

	public function testReadAllSuccessNoFilters() {
		$rules = [
			(new BadgeRule())->set_id(1),
			(new BadgeRule())->set_id(2)
		];

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::LIST_BADGE_RULES])
				)
		);

		$badgeRuleModel = $this->createMock(BadgeRuleModel::class);
		$badgeRuleModel->expects($this->once())->method('search')->willReturn($rules);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		self::assertSame($rules, $service->readAll([]));
	}

	#endregion test readAll()

	#region test update()

	public function testUpdateThrowsWhenNotAuthenticated() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_UNAUTHENTICATED_MODIFY);
		$service->update(1, 'not a file path');
	}

	public function testUpdateThrowsWhenNotAuthorized() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_UNAUTHORIZED_MODIFY);
		$service->update(1, 'not a file path');
	}

	public function testUpdateThrowsWhenFileIsNotReadable() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_INVALID_BADGE_RULE_DATA);
		// PHP warning is intentionally suppressed in next line for testing
		@$service->update(1, 'file_does_not_exist.json');
	}

	public function testUpdateThrowsWhenDataIsNotArray() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_INVALID_BADGE_RULE_DATA);
		$service->update(1, realpath(__DIR__ . '/BadgeRuleServiceTestData/UpdateThrowsWhenDataIsNotArray.json'));
	}

	public function testUpdateThrowsWhenNameIsNotSpecified() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_NAME_IS_REQUIRED);
		$service->update(1, realpath(__DIR__ . '/BadgeRuleServiceTestData/UpdateThrowsWhenNameIsNotSpecified.json'));
	}

	public function testUpdateThrowsWhenNameIsInvalid() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_NAME_IS_INVALID);
		$service->update(1, realpath(__DIR__ . '/BadgeRuleServiceTestData/UpdateThrowsWhenNameIsInvalid.json'));
	}

	public function testUpdateThrowsWhenEquipmentTypesIsWrongType() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_EQUIPMENT_TYPES_ARE_INVALID);
		$service->update(1, realpath(__DIR__ . '/BadgeRuleServiceTestData/UpdateThrowsWhenEquipmentTypesIsWrongType.json'));
	}

	public function testUpdateThrowsWhenEquipmentTypeIdIsInvalid() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('search')->willReturn([
			(new EquipmentType())->set_id(34),
			(new EquipmentType())->set_id(52)
		]);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_EQUIPMENT_TYPES_ARE_INVALID);
		$service->update(1, realpath(__DIR__ . '/BadgeRuleServiceTestData/UpdateThrowsWhenEquipmentTypeIdIsInvalid.json'));
	}

	public function testUpdateThrowsWhenEquipmentTypeDoesNotExist() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('search')->willReturn([
			(new EquipmentType())->set_id(30),
			(new EquipmentType())->set_id(58)
		]);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_EQUIPMENT_TYPES_ARE_INVALID);
		$service->update(1, realpath(__DIR__ . '/BadgeRuleServiceTestData/UpdateThrowsWhenEquipmentTypeDoesNotExist.json'));
	}

	public function testUpdateThrowsWhenEquipmentTypeNotFound() {
		$id = 12;
		$name = 'Electronics Technician'; // the sanitized name from the input file

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createMock(BadgeRuleModel::class);
		$badgeRuleModel->expects($this->once())->method('update')->with(
			$this->callback(
				fn(BadgeRule $rule) =>
					$rule->id() === $id
					&& $rule->name() === $name
			)
		)
		->willReturn(null);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('search')->willReturn([
			(new EquipmentType())->set_id(34),
			(new EquipmentType())->set_id(52)
		]);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		self::expectException(NotFoundException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_BADGE_RULE_NOT_FOUND);
		$service->update($id, realpath(__DIR__ . '/BadgeRuleServiceTestData/UpdateSuccess.json'));
	}

	public function testUpdateThrowsWhenLevelsAreInvalid() {
		$id = 12;
		$name = 'Electronics Technician'; // the sanitized name from the input file
		$equipment_type_ids = [34, 52];

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createMock(BadgeRuleModel::class);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('search')->willReturn([
			(new EquipmentType())->set_id(30),
			(new EquipmentType())->set_id(34),
			(new EquipmentType())->set_id(52),
			(new EquipmentType())->set_id(58)
		]);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_LEVELS_ARE_INVALID);
		$service->update($id, realpath(__DIR__ . '/BadgeRuleServiceTestData/UpdateThrowsWhenLevelsAreInvalid.json'));
	}

	public function testUpdateThrowsWhenLevelNameIsNotSpecified() {
		$id = 12;
		$name = 'Electronics Technician'; // the sanitized name from the input file
		$equipment_type_ids = [34, 52];

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createMock(BadgeRuleModel::class);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('search')->willReturn([
			(new EquipmentType())->set_id(30),
			(new EquipmentType())->set_id(34),
			(new EquipmentType())->set_id(52),
			(new EquipmentType())->set_id(58)
		]);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_LEVEL_IS_INVALID);
		$service->update($id, realpath(__DIR__ . '/BadgeRuleServiceTestData/UpdateThrowsWhenLevelNameIsNotSpecified.json'));
	}

	public function testUpdateThrowsWhenLevelNameIsNotString() {
		$id = 12;
		$name = 'Electronics Technician'; // the sanitized name from the input file
		$equipment_type_ids = [34, 52];

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createMock(BadgeRuleModel::class);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('search')->willReturn([
			(new EquipmentType())->set_id(30),
			(new EquipmentType())->set_id(34),
			(new EquipmentType())->set_id(52),
			(new EquipmentType())->set_id(58)
		]);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_LEVEL_IS_INVALID);
		$service->update($id, realpath(__DIR__ . '/BadgeRuleServiceTestData/UpdateThrowsWhenLevelNameIsNotString.json'));
	}

	public function testUpdateThrowsWhenLevelNameIsEmpty() {
		$id = 12;
		$name = 'Electronics Technician'; // the sanitized name from the input file
		$equipment_type_ids = [34, 52];

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createMock(BadgeRuleModel::class);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('search')->willReturn([
			(new EquipmentType())->set_id(30),
			(new EquipmentType())->set_id(34),
			(new EquipmentType())->set_id(52),
			(new EquipmentType())->set_id(58)
		]);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_LEVEL_IS_INVALID);
		$service->update($id, realpath(__DIR__ . '/BadgeRuleServiceTestData/UpdateThrowsWhenLevelNameIsEmpty.json'));
	}

	public function testUpdateThrowsWhenLevelUsesIsNotSpecified() {
		$id = 12;
		$name = 'Electronics Technician'; // the sanitized name from the input file
		$equipment_type_ids = [34, 52];

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createMock(BadgeRuleModel::class);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('search')->willReturn([
			(new EquipmentType())->set_id(30),
			(new EquipmentType())->set_id(34),
			(new EquipmentType())->set_id(52),
			(new EquipmentType())->set_id(58)
		]);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_LEVEL_IS_INVALID);
		$service->update($id, realpath(__DIR__ . '/BadgeRuleServiceTestData/UpdateThrowsWhenLevelUsesIsNotSpecified.json'));
	}

	public function testUpdateThrowsWhenLevelUsesIsInvalid() {
		$id = 12;
		$name = 'Electronics Technician'; // the sanitized name from the input file
		$equipment_type_ids = [34, 52];

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createMock(BadgeRuleModel::class);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('search')->willReturn([
			(new EquipmentType())->set_id(30),
			(new EquipmentType())->set_id(34),
			(new EquipmentType())->set_id(52),
			(new EquipmentType())->set_id(58)
		]);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_LEVEL_IS_INVALID);
		$service->update($id, realpath(__DIR__ . '/BadgeRuleServiceTestData/UpdateThrowsWhenLevelUsesIsInvalid.json'));
	}

	public function testUpdateSuccessNoEquipmentTypes() {
		$id = 12;
		$name = 'Electronics Technician'; // the sanitized name from the input file
		$level1_name = 'Novice';
		$level1_uses = 10;
		$level2_name = 'Pro';
		$level2_uses = 100;

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createMock(BadgeRuleModel::class);
		$badgeRuleModel->expects($this->once())->method('update')->with(
			$this->callback(
				fn(BadgeRule $rule) =>
					$rule->id() === $id
					&& $rule->name() === $name
			)
		)
		->willReturnArgument(0);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		$rule = $service->update($id, realpath(__DIR__ . '/BadgeRuleServiceTestData/UpdateSuccessNoEquipmentTypes.json'));

		self::assertInstanceOf(BadgeRule::class, $rule);
		self::assertSame($id, $rule->id());
		self::assertSame($name, $rule->name());
		$levels = $rule->levels();
		self::assertCount(2, $levels);
		$names = [];
		foreach ($levels as $level) {
			$name = $level->name();
			switch ($name) {
				case $level1_name:
					self::assertSame($level1_uses, $level->uses());
					break;
				case $level2_name:
					self::assertSame($level2_uses, $level->uses());
					break;
			}

			$names[] = $name;
		}
		self::assertEqualsCanonicalizing(
			$names,
			[
				$level1_name,
				$level2_name
			]
		);
	}

	public function testUpdateSuccessNoLevels() {
		$id = 12;
		$name = 'Electronics Technician'; // the sanitized name from the input file
		$equipment_type_ids = [34, 52];

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createMock(BadgeRuleModel::class);
		$badgeRuleModel->expects($this->once())->method('update')->with(
			$this->callback(
				fn(BadgeRule $rule) =>
					$rule->id() === $id
					&& $rule->name() === $name
					&& $rule->equipment_type_ids() === $equipment_type_ids
			)
		)
		->willReturnArgument(0);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('search')->willReturn([
			(new EquipmentType())->set_id(30),
			(new EquipmentType())->set_id(34),
			(new EquipmentType())->set_id(52),
			(new EquipmentType())->set_id(58)
		]);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		$rule = $service->update($id, realpath(__DIR__ . '/BadgeRuleServiceTestData/UpdateSuccessNoLevels.json'));

		self::assertInstanceOf(BadgeRule::class, $rule);
		self::assertSame($id, $rule->id());
		self::assertSame($name, $rule->name());
		self::assertSame($equipment_type_ids, $rule->equipment_type_ids());
		self::assertEmpty($rule->levels());
	}

	public function testUpdateSuccess() {
		$id = 12;
		$name = 'Electronics Technician'; // the sanitized name from the input file
		$equipment_type_ids = [34, 52];
		$level1_name = 'Novice';
		$level1_uses = 10;
		$level2_name = 'Pro';
		$level2_uses = 100;

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createMock(BadgeRuleModel::class);
		$badgeRuleModel->expects($this->once())->method('update')->with(
			$this->callback(
				fn(BadgeRule $rule) =>
					$rule->id() === $id
					&& $rule->name() === $name
					&& $rule->equipment_type_ids() === $equipment_type_ids
			)
		)
		->willReturnArgument(0);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('search')->willReturn([
			(new EquipmentType())->set_id(30),
			(new EquipmentType())->set_id(34),
			(new EquipmentType())->set_id(52),
			(new EquipmentType())->set_id(58)
		]);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		$rule = $service->update($id, realpath(__DIR__ . '/BadgeRuleServiceTestData/UpdateSuccess.json'));

		self::assertInstanceOf(BadgeRule::class, $rule);
		self::assertSame($id, $rule->id());
		self::assertSame($name, $rule->name());
		self::assertSame($equipment_type_ids, $rule->equipment_type_ids());
		$levels = $rule->levels();
		self::assertCount(2, $levels);
		$names = [];
		foreach ($levels as $level) {
			$name = $level->name();
			switch ($name) {
				case $level1_name:
					self::assertSame($level1_uses, $level->uses());
					break;
				case $level2_name:
					self::assertSame($level2_uses, $level->uses());
					break;
			}

			$names[] = $name;
		}
		self::assertEqualsCanonicalizing(
			$names,
			[
				$level1_name,
				$level2_name
			]
		);
	}

	#endregion test update()

	#region test delete()

	public function testDeleteThrowsWhenNotAuthenticated() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_UNAUTHENTICATED_DELETE);
		$service->delete(23);
	}

	public function testDeleteThrowsWhenNotAuthorized() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_UNAUTHORIZED_DELETE);
		$service->delete(23);
	}

	public function testDeleteThrowsWhenKeyDoesNotExist() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::DELETE_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		self::expectException(NotFoundException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_BADGE_RULE_NOT_FOUND);
		$service->delete(23);
	}

	public function testDeleteSuccess() {
		$rule = new BadgeRule();

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::DELETE_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);
		$badgeRuleModel->method('delete')->willReturn($rule);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel,
			$equipmentTypeModel
		);

		self::assertSame($rule, $service->delete(23));
	}

	#endregion test delete()
}
