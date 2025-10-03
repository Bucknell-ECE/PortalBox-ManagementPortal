<?php

declare(strict_types=1);

namespace Test\Portalbox\Service;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Portalbox\Entity\EquipmentType;
use Portalbox\Entity\Permission;
use Portalbox\Entity\Role;
use Portalbox\Entity\User;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Exception\NotFoundException;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Model\RoleModel;
use Portalbox\Model\UserModel;
use Portalbox\Query\UserQuery;
use Portalbox\Service\UserService;
use Portalbox\Session\SessionInterface;

final class UserServiceTest extends TestCase {
	#region test create()

	public function testCreateThrowsWhenNotAuthenticated() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(UserService::ERROR_UNAUTHENTICATED_CREATE);
		$service->create('');
	}

	public function testCreateThrowsWhenNotAuthorized() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(UserService::ERROR_UNAUTHORIZED_CREATE);
		$service->create('');
	}

	public function testCreateThrowsWhenFileIsNotReadable() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(1)
						->set_permissions([Permission::CREATE_USER])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_INVALID_JSON_DATA);
		// PHP warning is intentionally suppressed in next line for testing
		@$service->create('file_does_not_exist.json');
	}

	public function testCreateThrowsWhenDataIsNotArray() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(1)
						->set_permissions([Permission::CREATE_USER])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_INVALID_JSON_DATA);
		$service->create(realpath(__DIR__ . '/UserServiceTestData/CreateThrowsWhenDataIsNotArray.json'));
	}

	public function testCreateThrowsWhenRoleIdIsNotInteger() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(1)
						->set_permissions([Permission::CREATE_USER])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_ROLE_ID_IS_REQUIRED);
		$service->create(realpath(__DIR__ . '/UserServiceTestData/CreateThrowsWhenRoleIdIsNotInteger.json'));
	}

	public function testCreateThrowsWhenRoleDoesNotExist() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(1)
						->set_permissions([Permission::CREATE_USER])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_INVALID_ROLE_ID);
		$service->create(realpath(__DIR__ . '/UserServiceTestData/CreateThrowsWhenRoleDoesNotExist.json'));
	}

	public function testCreateThrowsWhenNameIsInvalid() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(1)
						->set_permissions([Permission::CREATE_USER])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$roleModel = $this->createStub(RoleModel::class);
		$roleModel->method('read')->willReturn(new Role());

		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_NAME_IS_REQUIRED);
		$service->create(realpath(__DIR__ . '/UserServiceTestData/CreateThrowsWhenNameIsInvalid.json'));
	}

	public function testCreateThrowsWhenEmailIsInvalid() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(1)
						->set_permissions([Permission::CREATE_USER])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$roleModel = $this->createStub(RoleModel::class);
		$roleModel->method('read')->willReturn(new Role());

		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_INVALID_EMAIL);
		$service->create(realpath(__DIR__ . '/UserServiceTestData/CreateThrowsWhenEmailIsInvalid.json'));
	}

	public function testCreateThrowsWhenInActiveIsInvalid() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(1)
						->set_permissions([Permission::CREATE_USER])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$roleModel = $this->createStub(RoleModel::class);
		$roleModel->method('read')->willReturn(new Role());

		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_INVALID_IS_ACTIVE);
		$service->create(realpath(__DIR__ . '/UserServiceTestData/CreateThrowsWhenInActiveIsInvalid.json'));
	}

	public function testCreateThrowsWhenAuthorizationsIsNotList() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(1)
						->set_permissions([Permission::CREATE_USER])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$roleModel = $this->createStub(RoleModel::class);
		$roleModel->method('read')->willReturn((new Role())->set_id(2));

		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_INVALID_AUTHORIZATIONS);
		$service->create(realpath(__DIR__ . '/UserServiceTestData/CreateThrowsWhenAuthorizationsIsNotList.json'));
	}

	public function testCreateThrowsWhenAuthorizationIsInvalid() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(1)
						->set_permissions([Permission::CREATE_USER])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('search')->willReturn([]);

		$roleModel = $this->createStub(RoleModel::class);
		$roleModel->method('read')->willReturn((new Role())->set_id(2));

		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_INVALID_AUTHORIZATIONS);
		$service->create(realpath(__DIR__ . '/UserServiceTestData/CreateThrowsWhenAuthorizationIsInvalid.json'));
	}

	public function testCreateSuccess() {
		$role = (new Role())
			->set_id(2);

		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(1)
						->set_permissions([Permission::CREATE_USER])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('search')->willReturn([]);

		$roleModel = $this->createStub(RoleModel::class);
		$roleModel->method('read')->willReturn($role);

		$userModel = $this->createStub(UserModel::class);
		$userModel->expects($this->once())->method('create')->with(
			$this->callback(
				fn(User $user) =>
					$user instanceof User
					&& $user->name() === 'Maker'
					&& $user->email() === 'maker@makerspace.tld'
					&& $user->is_active()
					&& $user->role() === $role
			)
		)->willReturnArgument(0);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		$user = $service->create(realpath(__DIR__ . '/UserServiceTestData/CreateSuccess.json'));

		self::assertInstanceOf(User::class, $user);
		self::assertSame('Maker', $user->name());
		self::assertSame('maker@makerspace.tld', $user->email());
		self::assertSame(true, $user->is_active());
		self::assertSame($role, $user->role());
		self::assertSame(null, $user->comment());
		self::assertSame([], $user->authorizations());
	}

	public function testCreateSuccessWithOptionalValues() {
		$role = (new Role())
			->set_id(2);

		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(1)
						->set_permissions([Permission::CREATE_USER])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('search')->willReturn([
			(new EquipmentType())->set_id(23)
		]);

		$roleModel = $this->createStub(RoleModel::class);
		$roleModel->method('read')->willReturn($role);

		$userModel = $this->createStub(UserModel::class);
		$userModel->expects($this->once())->method('create')->with(
			$this->callback(
				fn(User $user) =>
					$user instanceof User
					&& $user->name() === 'Maker'
					&& $user->email() === 'maker@makerspace.tld'
					&& $user->is_active()
					&& $user->role() === $role
			)
		)->willReturnArgument(0);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		$user = $service->create(realpath(__DIR__ . '/UserServiceTestData/CreateSuccessWithOptionalValues.json'));

		self::assertInstanceOf(User::class, $user);
		self::assertSame('Maker', $user->name());
		self::assertSame('maker@makerspace.tld', $user->email());
		self::assertSame(true, $user->is_active());
		self::assertSame($role, $user->role());
		self::assertSame('new maker', $user->comment());
		self::assertSame([23], $user->authorizations());
	}

	#endregion test create()

	#region test import()

	public function testImportThrowsWhenNotAuthenticated() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(UserService::ERROR_UNAUTHENTICATED_CREATE);
		$service->import('');
	}

	public function testImportThrowsWhenNotAuthorized() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(UserService::ERROR_UNAUTHORIZED_CREATE);
		$service->import('');
	}

	public function testImportThrowsWhenLineTooShort() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(1)
						->set_permissions([Permission::CREATE_USER])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$roleModel = $this->createStub(RoleModel::class);
		$roleModel->method('search')->willReturn([
			(new Role())->set_name('admin')
		]);

		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_INVALID_CSV_RECORD_LENGTH);
		$service->import(realpath(__DIR__ . '/UserServiceTestData/ImportThrowsWhenLineTooShort.csv'));
	}

	public function testImportThrowsWhenLineTooLong() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(1)
						->set_permissions([Permission::CREATE_USER])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$roleModel = $this->createStub(RoleModel::class);
		$roleModel->method('search')->willReturn([
			(new Role())->set_name('admin')
		]);

		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_INVALID_CSV_RECORD_LENGTH);
		$service->import(realpath(__DIR__ . '/UserServiceTestData/ImportThrowsWhenLineTooLong.csv'));
	}

	public function testImportThrowsWhenRoleDoesNotExist() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(1)
						->set_permissions([Permission::CREATE_USER])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$roleModel = $this->createStub(RoleModel::class);
		$roleModel->method('search')->willReturn([
			(new Role())->set_name('user')
		]);

		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_INVALID_CSV_ROLE);
		$service->import(realpath(__DIR__ . '/UserServiceTestData/ImportThrowsWhenRoleDoesNotExist.csv'));
	}

	public function testImportThrowsWhenNameIsInvalid() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(1)
						->set_permissions([Permission::CREATE_USER])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$roleModel = $this->createStub(RoleModel::class);
		$roleModel->method('search')->willReturn([
			(new Role())->set_name('admin')
		]);

		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_NAME_IS_REQUIRED);
		$service->import(realpath(__DIR__ . '/UserServiceTestData/ImportThrowsWhenNameIsInvalid.csv'));
	}

	public function testImportThrowsWhenEmailIsInvalid() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(1)
						->set_permissions([Permission::CREATE_USER])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$roleModel = $this->createStub(RoleModel::class);
		$roleModel->method('search')->willReturn([
			(new Role())->set_name('admin')
		]);

		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_INVALID_EMAIL);
		$service->import(realpath(__DIR__ . '/UserServiceTestData/ImportThrowsWhenEmailIsInvalid.csv'));
	}

	public function testImportSuccess() {
		$role = (new Role())->set_id(3)->set_name('admin');

		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(1)
						->set_permissions([Permission::CREATE_USER])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$roleModel = $this->createStub(RoleModel::class);
		$roleModel->method('search')->willReturn([$role]);

		$userModel = $this->createMock(UserModel::class);
		$userModel->expects($this->once())->method('create')->with(
			$this->callback(
				fn(User $user) =>
					$user instanceof User
					&& $user->name() === 'Makerspace Administrator'
					&& $user->email() === 'admin@makerspace.tld'
					&& $user->is_active()
					&& $user->role() === $role
			)
		)->willReturnArgument(0);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		$users = $service->import(realpath(__DIR__ . '/UserServiceTestData/ImportSuccess.csv'));
		self::assertIsArray($users);
		self::assertCount(1, $users);
		$user = $users[0];
		self::assertInstanceOf(User::class, $user);
		self::assertSame('Makerspace Administrator', $user->name());
		self::assertSame('admin@makerspace.tld', $user->email());
		self::assertTrue($user->is_active());
		self::assertSame($role, $user->role());
	}

	#endregion test import()

	#region test read()

	public function testReadThrowsWhenNotAuthenticated() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(UserService::ERROR_UNAUTHENTICATED_READ);
		$service->read(1);
	}

	public function testReadThrowsWhenNotAuthorizedToReadSelf() {
		$authenticatedUserId = 12;

		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_id($authenticatedUserId)
				->set_role((new Role())->set_id(2))
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(UserService::ERROR_UNAUTHORIZED_READ);
		$service->read($authenticatedUserId);
	}

	public function testReadThrowsWhenNotAuthorizedToReadOthers() {
		$authenticatedUserId = 12;

		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_id($authenticatedUserId)
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::READ_OWN_USER])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(UserService::ERROR_UNAUTHORIZED_READ);
		$service->read($authenticatedUserId + 1);
	}

	public function testReadThrowsWhenUserDoesNotExist() {
		$authenticatedUserId = 12;

		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_id($authenticatedUserId)
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::READ_USER])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);
		$userModel = $this->createStub(UserModel::class);
		$userModel->method('read')->willReturn(null);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(NotFoundException::class);
		self::expectExceptionMessage(UserService::ERROR_USER_NOT_FOUND);
		$service->read(1);
	}

	public function testReadAllowsUserToReadOthers() {
		$authenticatedUserId = 12;
		$user = new User();

		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_id($authenticatedUserId)
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::READ_USER])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);
		$userModel = $this->createStub(UserModel::class);
		$userModel->method('read')->willReturn($user);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::assertSame($user, $service->read($authenticatedUserId + 1));
	}

	public function testReadAllowsUserToReadSelf() {
		$authenticatedUserId = 12;
		$user = new User();

		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_id($authenticatedUserId)
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::READ_OWN_USER])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);
		$userModel = $this->createStub(UserModel::class);
		$userModel->method('read')->willReturn($user);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::assertSame($user, $service->read($authenticatedUserId));
	}

	#endregion test read()

	#region test readAll()

	public function testReadAllThrowsWhenNotAuthenticated() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(UserService::ERROR_UNAUTHENTICATED_READ);
		$service->readAll([]);
	}

	public function testReadAllThrowsWhenNotAuthorized() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(UserService::ERROR_UNAUTHORIZED_READ);
		$service->readAll([]);
	}

	public function testReadAllThrowsWhenNotInactiveFilterIsNotBoolean() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::LIST_USERS])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_INACTIVE_FILTER_MUST_BE_BOOL);
		$service->readAll(['include_inactive' => 'meh']);
	}

	public function testReadAllThrowsWhenNotRoleFilterIsNotInteger() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::LIST_USERS])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_ROLE_FILTER_MUST_BE_INT);
		$service->readAll(['role_id' => 'meh']);
	}

	public function testReadAllThrowsWhenNotEquipmentFilterIsNotInteger() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::LIST_USERS])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_EQUIPMENT_FILTER_MUST_BE_INT);
		$service->readAll(['equipment_id' => 'meh']);
	}

	/**
	 * @dataProvider getReadAllFilters
	 */
	public function testReadAllSuccess(
		$filters,
		$inactive,
		$name,
		$email,
		$role_id,
		$comment,
		$equipment_id
	) {
		$users = [
			new User()
		];

		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::LIST_USERS])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);
		$userModel = $this->createStub(UserModel::class);
		$userModel->expects($this->once())->method('search')->with(
			$this->callback(
				fn(UserQuery $query) =>
					$query->include_inactive() === $inactive
					&& $query->name() === $name
					&& $query->email() === $email
					&& $query->role_id() === $role_id
					&& $query->comment() === $comment
					&& $query->equipment_id() === $equipment_id
			)
		)
		->willReturn($users);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::assertSame($users, $service->readAll($filters));
	}

	public static function getReadAllFilters(): iterable {
		yield [
			['include_inactive' => '1'],
			true,
			NULL,
			NULL,
			NULL,
			NULL,
			NULL
		];

		yield [
			['name' => 'Sebastian'],
			NULL,
			'Sebastian',
			NULL,
			NULL,
			NULL,
			NULL
		];

		yield [
			['email' => 'sebastian@makerspace.tld'],
			NULL,
			NULL,
			'sebastian@makerspace.tld',
			NULL,
			NULL,
			NULL
		];

		yield [
			['role_id' => '2'],
			NULL,
			NULL,
			NULL,
			2,
			NULL,
			NULL
		];

		yield [
			['comment' => 'experienced crafter'],
			NULL,
			NULL,
			NULL,
			NULL,
			'experienced crafter',
			NULL
		];

		yield [
			['equipment_id' => '6'],
			NULL,
			NULL,
			NULL,
			NULL,
			NULL,
			6
		];
	}

	#endregion test readAll()

	#region test update()

	public function testUpdateThrowsWhenNotAuthenticated() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(UserService::ERROR_UNAUTHENTICATED_WRITE);
		$service->update(1, '');
	}

	public function testUpdateThrowsWhenNotAuthorized() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(UserService::ERROR_UNAUTHORIZED_MODIFY);
		$service->update(1, '');
	}

	public function testUpdateThrowsWhenFileIsNotReadable() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(1)
						->set_permissions([Permission::MODIFY_USER])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_INVALID_JSON_DATA);
		// PHP warning is intentionally suppressed in next line for testing
		@$service->update(1, 'file_does_not_exist.json');
	}

	public function testUpdateThrowsWhenDataIsNotArray() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(1)
						->set_permissions([Permission::MODIFY_USER])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_INVALID_JSON_DATA);
		$service->update(1, realpath(__DIR__ . '/UserServiceTestData/UpdateThrowsWhenDataIsNotArray.json'));
	}

	public function testUpdateThrowsWhenRoleIdIsNotInteger() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(1)
						->set_permissions([Permission::MODIFY_USER])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_ROLE_ID_IS_REQUIRED);
		$service->update(1, realpath(__DIR__ . '/UserServiceTestData/UpdateThrowsWhenRoleIdIsNotInteger.json'));
	}

	public function testUpdateThrowsWhenRoleDoesNotExist() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(1)
						->set_permissions([Permission::MODIFY_USER])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_INVALID_ROLE_ID);
		$service->update(1, realpath(__DIR__ . '/UserServiceTestData/UpdateThrowsWhenRoleDoesNotExist.json'));
	}

	public function testUpdateThrowsWhenNameIsInvalid() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(1)
						->set_permissions([Permission::MODIFY_USER])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$roleModel = $this->createStub(RoleModel::class);
		$roleModel->method('read')->willReturn(new Role());

		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_NAME_IS_REQUIRED);
		$service->update(1, realpath(__DIR__ . '/UserServiceTestData/UpdateThrowsWhenNameIsInvalid.json'));
	}

	public function testUpdateThrowsWhenEmailIsInvalid() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(1)
						->set_permissions([Permission::MODIFY_USER])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$roleModel = $this->createStub(RoleModel::class);
		$roleModel->method('read')->willReturn(new Role());

		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_INVALID_EMAIL);
		$service->update(1, realpath(__DIR__ . '/UserServiceTestData/UpdateThrowsWhenEmailIsInvalid.json'));
	}

	public function testUpdateThrowsWhenInActiveIsInvalid() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(1)
						->set_permissions([Permission::MODIFY_USER])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$roleModel = $this->createStub(RoleModel::class);
		$roleModel->method('read')->willReturn(new Role());

		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_INVALID_IS_ACTIVE);
		$service->update(1, realpath(__DIR__ . '/UserServiceTestData/UpdateThrowsWhenInActiveIsInvalid.json'));
	}

	public function testUpdateThrowsWhenAuthorizationsIsNotList() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(1)
						->set_permissions([Permission::MODIFY_USER])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$roleModel = $this->createStub(RoleModel::class);
		$roleModel->method('read')->willReturn((new Role())->set_id(2));

		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_INVALID_AUTHORIZATIONS);
		$service->update(1, realpath(__DIR__ . '/UserServiceTestData/UpdateThrowsWhenAuthorizationsIsNotList.json'));
	}

	public function testUpdateThrowsWhenAuthorizationIsInvalid() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(1)
						->set_permissions([Permission::MODIFY_USER])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('search')->willReturn([]);

		$roleModel = $this->createStub(RoleModel::class);
		$roleModel->method('read')->willReturn((new Role())->set_id(2));

		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_INVALID_AUTHORIZATIONS);
		$service->update(1, realpath(__DIR__ . '/UserServiceTestData/UpdateThrowsWhenAuthorizationIsInvalid.json'));
	}

	public function testUpdateThrowsWhenUserDoesNotExist() {
		$role = (new Role())
			->set_id(2);

		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(1)
						->set_permissions([Permission::MODIFY_USER])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('search')->willReturn([]);

		$roleModel = $this->createStub(RoleModel::class);
		$roleModel->method('read')->willReturn($role);

		$userModel = $this->createStub(UserModel::class);
		$userModel->method('update')->willReturn(null);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(NotFoundException::class);
		self::expectExceptionMessage(UserService::ERROR_USER_NOT_FOUND);
		$service->update(1, realpath(__DIR__ . '/UserServiceTestData/UpdateSuccess.json'));
	}

	public function testUpdateSuccess() {
		$user_id = 42;

		$role = (new Role())
			->set_id(2);

		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(1)
						->set_permissions([Permission::MODIFY_USER])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('search')->willReturn([]);

		$roleModel = $this->createStub(RoleModel::class);
		$roleModel->method('read')->willReturn($role);

		$userModel = $this->createStub(UserModel::class);
		$userModel->expects($this->once())->method('update')->with(
			$this->callback(
				fn(User $user) =>
					$user instanceof User
					&& $user->id() === $user_id
					&& $user->name() === 'Maker'
					&& $user->email() === 'maker@makerspace.tld'
					&& $user->is_active()
					&& $user->role() === $role
			)
		)->willReturnArgument(0);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		$user = $service->update($user_id, realpath(__DIR__ . '/UserServiceTestData/UpdateSuccess.json'));

		self::assertInstanceOf(User::class, $user);
		self::assertSame('Maker', $user->name());
		self::assertSame('maker@makerspace.tld', $user->email());
		self::assertSame(true, $user->is_active());
		self::assertSame($role, $user->role());
		self::assertSame(null, $user->comment());
		self::assertSame([], $user->authorizations());
	}

	public function testUpdateSuccessWithOptionalValues() {
		$user_id = 42;

		$role = (new Role())
			->set_id(2);

		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(1)
						->set_permissions([Permission::MODIFY_USER])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('search')->willReturn([
			(new EquipmentType())->set_id(23)
		]);

		$roleModel = $this->createStub(RoleModel::class);
		$roleModel->method('read')->willReturn($role);

		$userModel = $this->createStub(UserModel::class);
		$userModel->expects($this->once())->method('update')->with(
			$this->callback(
				fn(User $user) =>
					$user instanceof User
					&& $user->id() === $user_id
					&& $user->name() === 'Maker'
					&& $user->email() === 'maker@makerspace.tld'
					&& $user->is_active()
					&& $user->role() === $role
			)
		)->willReturnArgument(0);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		$user = $service->update($user_id , realpath(__DIR__ . '/UserServiceTestData/UpdateSuccessWithOptionalValues.json'));

		self::assertInstanceOf(User::class, $user);
		self::assertSame('Maker', $user->name());
		self::assertSame('maker@makerspace.tld', $user->email());
		self::assertSame(true, $user->is_active());
		self::assertSame($role, $user->role());
		self::assertSame('new maker', $user->comment());
		self::assertSame([23], $user->authorizations());
	}

	#endregion test update()

	#region test patch()

	public function testPatchThrowsWhenNotAuthenticated() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(UserService::ERROR_UNAUTHENTICATED_WRITE);
		$service->patch(1, '');
	}

	public function testPatchThrowsWhenUserDoesNotExist() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(new User());

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);

		$userModel = $this->createStub(UserModel::class);
		$userModel->method('read')->willReturn(null);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(NotFoundException::class);
		self::expectExceptionMessage(UserService::ERROR_USER_NOT_FOUND);
		$service->patch(1, '');
	}

	public function testPatchThrowsWhenFileIsNotReadable() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(new User());

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);

		$userModel = $this->createStub(UserModel::class);
		$userModel->method('read')->willReturn(new User());

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_INVALID_PATCH);
		// PHP warning is intentionally suppressed in next line for testing
		@$service->patch(1, 'file_does_not_exist.json');
	}

	public function testPatchThrowsWhenDataIsNotArray() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(new User());

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);

		$userModel = $this->createStub(UserModel::class);
		$userModel->method('read')->willReturn(new User());

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_INVALID_PATCH);
		$service->patch(1, realpath(__DIR__ . '/UserServiceTestData/PatchThrowsWhenDataIsNotArray.json'));
	}

	public function testPatchThrowsWhenPatchIncludesUnsupportedProperty() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(new User());

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);

		$userModel = $this->createStub(UserModel::class);
		$userModel->method('read')->willReturn(new User());

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_INVALID_PATCH);
		$service->patch(1, realpath(__DIR__ . '/UserServiceTestData/PatchThrowsWhenPatchIncludesUnsupportedProperty.json'));
	}

	#region test patch(authorizations)

	public function testPatchAuthorizationThrowsNotAuthorized() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);

		$userModel = $this->createStub(UserModel::class);
		$userModel->method('read')->willReturn(new User());

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(UserService::ERROR_NOT_AUTHORIZED_TO_PATCH_AUTHORIZATIONS);
		$service->patch(1, realpath(__DIR__ . '/UserServiceTestData/PatchAuthorizationSuccess.json'));
	}

	public function testPatchAuthorizationThrowsWhenAuthorizationsNotArray() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([
							Permission::CREATE_EQUIPMENT_AUTHORIZATION,
							Permission::DELETE_EQUIPMENT_AUTHORIZATION,
							Permission::MODIFY_USER
						])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);

		$userModel = $this->createStub(UserModel::class);
		$userModel->method('read')->willReturn(new User());

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_INVALID_AUTHORIZATIONS);
		$service->patch(1, realpath(__DIR__ . '/UserServiceTestData/PatchAuthorizationThrowsWhenAuthorizationsNotArray.json'));
	}

	public function testPatchAuthorizationThrowsWhenAuthorizationIsNotInt() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([
							Permission::CREATE_EQUIPMENT_AUTHORIZATION,
							Permission::DELETE_EQUIPMENT_AUTHORIZATION,
							Permission::MODIFY_USER
						])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);

		$userModel = $this->createStub(UserModel::class);
		$userModel->method('read')->willReturn(new User());

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_INVALID_AUTHORIZATIONS);
		$service->patch(1, realpath(__DIR__ . '/UserServiceTestData/PatchAuthorizationThrowsWhenAuthorizationIsNotInt.json'));
	}

	public function testPatchAuthorizationThrowsWhenEquipmentTypeDoesNotExist() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([
							Permission::CREATE_EQUIPMENT_AUTHORIZATION,
							Permission::DELETE_EQUIPMENT_AUTHORIZATION,
							Permission::MODIFY_USER
						])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('read')->willReturn(null);

		$roleModel = $this->createStub(RoleModel::class);

		$userModel = $this->createStub(UserModel::class);
		$userModel->method('read')->willReturn(new User());

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_INVALID_AUTHORIZATIONS);
		$service->patch(1, realpath(__DIR__ . '/UserServiceTestData/PatchAuthorizationThrowsWhenEquipmentTypeDoesNotExist.json'));
	}

	public function testPatchAuthorizationSuccess() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([
							Permission::CREATE_EQUIPMENT_AUTHORIZATION,
							Permission::DELETE_EQUIPMENT_AUTHORIZATION,
							Permission::MODIFY_USER
						])
				)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('read')->willReturn(new EquipmentType());

		$roleModel = $this->createStub(RoleModel::class);

		$userModel = $this->createStub(UserModel::class);
		$userModel->method('read')->willReturn(new User());
		$userModel->expects($this->once())->method('update')->with(
			$this->callback(
				fn(User $user) =>
					$user instanceof User
					&& $user->authorizations() === [9, 10]
			)
		)->willReturnArgument(0);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		$user = $service->patch(1, realpath(__DIR__ . '/UserServiceTestData/PatchAuthorizationSuccess.json'));
		self::assertInstanceOf(User::class, $user);
		self::assertSame([9, 10], $user->authorizations());
	}

	#endregion test patch(authorizations)

	#region test patch(pin)

	public function testPatchPINThrowsWhenNotAuthorized() {
		$authenticatedUserId = 501;

		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())->set_id($authenticatedUserId)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);

		$userModel = $this->createStub(UserModel::class);
		$userModel->method('read')->willReturn(
			(new User())->set_id($authenticatedUserId + 1)
		);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(UserService::ERROR_NOT_AUTHORIZED_TO_PATCH_PIN);
		$service->patch(1, realpath(__DIR__ . '/UserServiceTestData/PatchPINSuccess.json'));
	}

	public function testPatchPINThrowsWhenNotString() {
		$authenticatedUserId = 501;

		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())->set_id($authenticatedUserId)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);

		$userModel = $this->createStub(UserModel::class);
		$userModel->method('read')->willReturn(
			(new User())->set_id($authenticatedUserId)
		);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_INVALID_PIN);
		$service->patch(1, realpath(__DIR__ . '/UserServiceTestData/PatchPINThrowsWhenNotString.json'));
	}

	public function testPatchPINThrowsWhenNotFourDigits() {
		$authenticatedUserId = 501;

		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())->set_id($authenticatedUserId)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);

		$userModel = $this->createStub(UserModel::class);
		$userModel->method('read')->willReturn(
			(new User())->set_id($authenticatedUserId)
		);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_INVALID_PIN);
		$service->patch(1, realpath(__DIR__ . '/UserServiceTestData/PatchPINThrowsWhenNotFourDigits.json'));
	}

	public function testPatchPINSuccess() {
		$authenticatedUserId = 501;

		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())->set_id($authenticatedUserId)
		);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);

		$userModel = $this->createStub(UserModel::class);
		$userModel->method('read')->willReturn(
			(new User())->set_id($authenticatedUserId)
		);
		$userModel->expects($this->once())->method('update')->with(
			$this->callback(
				fn(User $user) =>
					$user instanceof User
					&& password_verify('1234', $user->pin())
			)
		)->willReturnArgument(0);

		$service = new UserService(
			$session,
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		$user = $service->patch(1, realpath(__DIR__ . '/UserServiceTestData/PatchPINSuccess.json'));
		self::assertInstanceOf(User::class, $user);
		self::assertTrue(password_verify('1234', $user->pin()));
	}

	#endregion test patch(pin)

	#endregion test patch()
}
