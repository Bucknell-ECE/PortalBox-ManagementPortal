<?php

declare(strict_types=1);

namespace Test\Portalbox\Service;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Portalbox\Enumeration\Permission;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Exception\NotFoundException;
use Portalbox\Model\RoleModel;
use Portalbox\Service\RoleService;
use Portalbox\Session;
use Portalbox\Type\Role;
use Portalbox\Type\User;

final class RoleServiceTest extends TestCase {
	#region test create()

	public function testCreateThrowsWhenNotAuthenticated() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$roleModel = $this->createStub(RoleModel::class);

		$service = new RoleService(
			$session,
			$roleModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(RoleService::ERROR_UNAUTHENTICATED_CREATE);
		$service->create('not a file path');
	}

	public function testCreateThrowsWhenNotAuthorized() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$roleModel = $this->createStub(RoleModel::class);

		$service = new RoleService(
			$session,
			$roleModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(RoleService::ERROR_UNAUTHORIZED_CREATE);
		$service->create('not a file path');
	}

	public function testCreateThrowsWhenFileIsNotReadable() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_ROLE])
				)
		);

		$roleModel = $this->createStub(RoleModel::class);

		$service = new RoleService(
			$session,
			$roleModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(RoleService::ERROR_INVALID_ROLE_DATA);
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
						->set_permissions([Permission::CREATE_ROLE])
				)
		);

		$roleModel = $this->createStub(RoleModel::class);

		$service = new RoleService(
			$session,
			$roleModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(RoleService::ERROR_INVALID_ROLE_DATA);
		$service->create(realpath(__DIR__ . '/RoleServiceTestData/CreateThrowsWhenDataIsNotArray.json'));
	}

	public function testCreateThrowsWhenNameIsNotSpecified() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_ROLE])
				)
		);

		$roleModel = $this->createStub(RoleModel::class);

		$service = new RoleService(
			$session,
			$roleModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(RoleService::ERROR_NAME_IS_REQUIRED);
		$service->create(realpath(__DIR__ . '/RoleServiceTestData/CreateThrowsWhenNameIsNotSpecified.json'));
	}

	public function testCreateThrowsWhenNameIsInvalid() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_ROLE])
				)
		);

		$roleModel = $this->createStub(RoleModel::class);

		$service = new RoleService(
			$session,
			$roleModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(RoleService::ERROR_NAME_IS_INVALID);
		$service->create(realpath(__DIR__ . '/RoleServiceTestData/CreateThrowsWhenNameIsInvalid.json'));
	}

	public function testCreateThrowsWhenDescriptionIsNotSpecified() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_ROLE])
				)
		);

		$roleModel = $this->createStub(RoleModel::class);

		$service = new RoleService(
			$session,
			$roleModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(RoleService::ERROR_DESCRIPTION_IS_REQUIRED);
		$service->create(realpath(__DIR__ . '/RoleServiceTestData/CreateThrowsWhenDescriptionIsNotSpecified.json'));
	}

	public function testCreateThrowsWhenPermissionsAreNotSpecified() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_ROLE])
				)
		);

		$roleModel = $this->createStub(RoleModel::class);

		$service = new RoleService(
			$session,
			$roleModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(RoleService::ERROR_PERMISSIONS_ARE_REQUIRED);
		$service->create(realpath(__DIR__ . '/RoleServiceTestData/CreateThrowsWhenPermissionsAreNotSpecified.json'));
	}

	public function testCreateThrowsWhenPermissionsAreNotList() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_ROLE])
				)
		);

		$roleModel = $this->createStub(RoleModel::class);

		$service = new RoleService(
			$session,
			$roleModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(RoleService::ERROR_PERMISSIONS_ARE_INVALID);
		$service->create(realpath(__DIR__ . '/RoleServiceTestData/CreateThrowsWhenPermissionsAreNotList.json'));
	}

	public function testCreateThrowsWhenPermissionsAreInvalidType() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_ROLE])
				)
		);

		$roleModel = $this->createStub(RoleModel::class);

		$service = new RoleService(
			$session,
			$roleModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(RoleService::ERROR_PERMISSIONS_ARE_INVALID);
		$service->create(realpath(__DIR__ . '/RoleServiceTestData/CreateThrowsWhenPermissionsAreInvalidType.json'));
	}

	public function testCreateThrowsWhenPermissionIsInvalid() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_ROLE])
				)
		);

		$roleModel = $this->createStub(RoleModel::class);

		$service = new RoleService(
			$session,
			$roleModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(RoleService::ERROR_PERMISSIONS_ARE_INVALID);
		$service->create(realpath(__DIR__ . '/RoleServiceTestData/CreateThrowsWhenPermissionIsInvalid.json'));
	}

	public function testCreateThrowsWhenPermissionsAreInvalid() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_ROLE])
				)
		);

		$roleModel = $this->createStub(RoleModel::class);

		$service = new RoleService(
			$session,
			$roleModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(RoleService::ERROR_PERMISSIONS_ARE_INVALID);
		$service->create(realpath(__DIR__ . '/RoleServiceTestData/CreateThrowsWhenPermissionsAreInvalid.json'));
	}

	public function testCreateSuccess() {
		$name = 'Limited User'; // the sanitized name from the input file
		$description = 'Important People'; // the sanitized description from the input file
		$permissions = [
			Permission::LIST_OWN_EQUIPMENT_AUTHORIZATIONS,
			Permission::LIST_OWN_CARDS,
			Permission::LIST_OWN_CHARGES,
			Permission::LIST_OWN_PAYMENTS,
			Permission::READ_OWN_USER,
		];

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_ROLE])
				)
		);

		$roleModel = $this->createMock(RoleModel::class);
		$roleModel->expects($this->once())->method('create')->with(
			$this->callback(
				fn(Role $role) =>
					$role->name() === $name
					&& $role->description() === $description
					&& $role->permissions() === $permissions
					&& $role->is_system_role() === false
			)
		)
		->willReturnArgument(0);

		$service = new RoleService(
			$session,
			$roleModel
		);

		$role = $service->create(realpath(__DIR__ . '/RoleServiceTestData/CreateSuccess.json'));

		self::assertInstanceOf(Role::class, $role);
		self::assertSame($name, $role->name());
		self::assertSame($description, $role->description());
		self::assertSame($permissions, $role->permissions());
		self::assertFalse($role->is_system_role());
	}

	#endregion test create()

	#region test read()

	public function testReadThrowsWhenNotAuthenticated() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$roleModel = $this->createStub(RoleModel::class);

		$service = new RoleService(
			$session,
			$roleModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(RoleService::ERROR_UNAUTHENTICATED_READ);
		$service->read(123456789);
	}

	public function testReadThrowsWhenNotAuthorized() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$roleModel = $this->createStub(RoleModel::class);

		$service = new RoleService(
			$session,
			$roleModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(RoleService::ERROR_UNAUTHORIZED_READ);
		$service->read(123456789);
	}

	public function testReadThrowsWhenRoleDoesNotExist() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::READ_ROLE])
				)
		);

		$roleModel = $this->createStub(RoleModel::class);

		$service = new RoleService(
			$session,
			$roleModel
		);

		self::expectException(NotFoundException::class);
		self::expectExceptionMessage(RoleService::ERROR_ROLE_NOT_FOUND);
		$service->read(123456789);
	}

	public function testReadSuccess() {
		$role = new Role();

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::READ_ROLE])
				)
		);

		$roleModel = $this->createStub(RoleModel::class);
		$roleModel->method('read')->willReturn($role);

		$service = new RoleService(
			$session,
			$roleModel
		);

		self::assertSame($role, $service->read(123456789));
	}

	#endregion test read()

	#region test readAll()

	public function testReadAllThrowsWhenNotAuthenticated() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$roleModel = $this->createStub(RoleModel::class);

		$service = new RoleService(
			$session,
			$roleModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(RoleService::ERROR_UNAUTHENTICATED_READ);
		$service->readAll();
	}

	public function testReadAllThrowsWhenNotAuthorized() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$roleModel = $this->createStub(RoleModel::class);

		$service = new RoleService(
			$session,
			$roleModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(RoleService::ERROR_UNAUTHORIZED_READ);
		$service->readAll();
	}

	public function testReadAllSuccess() {
		$roles = [
			(new Role())->set_id(1),
			(new Role())->set_id(2)
		];

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::LIST_ROLES])
				)
		);

		$roleModel = $this->createMock(RoleModel::class);
		$roleModel->expects($this->once())->method('search')->willReturn($roles);

		$service = new RoleService(
			$session,
			$roleModel
		);

		self::assertSame($roles, $service->readAll());
	}

	#endregion test readAll()

	#region test update()

	public function testUpdateThrowsWhenNotAuthenticated() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$roleModel = $this->createStub(RoleModel::class);

		$service = new RoleService(
			$session,
			$roleModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(RoleService::ERROR_UNAUTHENTICATED_MODIFY);
		$service->update(1, 'not a file path');
	}

	public function testUpdateThrowsWhenNotAuthorized() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$roleModel = $this->createStub(RoleModel::class);

		$service = new RoleService(
			$session,
			$roleModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(RoleService::ERROR_UNAUTHORIZED_MODIFY);
		$service->update(1, 'not a file path');
	}

	public function testUpdateThrowsWhenFileIsNotReadable() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_ROLE])
				)
		);

		$roleModel = $this->createStub(RoleModel::class);

		$service = new RoleService(
			$session,
			$roleModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(RoleService::ERROR_INVALID_ROLE_DATA);
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
						->set_permissions([Permission::MODIFY_ROLE])
				)
		);

		$roleModel = $this->createStub(RoleModel::class);

		$service = new RoleService(
			$session,
			$roleModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(RoleService::ERROR_INVALID_ROLE_DATA);
		$service->update(1, realpath(__DIR__ . '/RoleServiceTestData/UpdateThrowsWhenDataIsNotArray.json'));
	}

	public function testUpdateThrowsWhenNameIsNotSpecified() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_ROLE])
				)
		);

		$roleModel = $this->createStub(RoleModel::class);

		$service = new RoleService(
			$session,
			$roleModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(RoleService::ERROR_NAME_IS_REQUIRED);
		$service->update(1, realpath(__DIR__ . '/RoleServiceTestData/UpdateThrowsWhenNameIsNotSpecified.json'));
	}

	public function testUpdateThrowsWhenNameIsInvalid() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_ROLE])
				)
		);

		$roleModel = $this->createStub(RoleModel::class);

		$service = new RoleService(
			$session,
			$roleModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(RoleService::ERROR_NAME_IS_INVALID);
		$service->update(1, realpath(__DIR__ . '/RoleServiceTestData/UpdateThrowsWhenNameIsInvalid.json'));
	}

	public function testUpdateThrowsWhenDescriptionIsNotSpecified() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_ROLE])
				)
		);

		$roleModel = $this->createStub(RoleModel::class);

		$service = new RoleService(
			$session,
			$roleModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(RoleService::ERROR_DESCRIPTION_IS_REQUIRED);
		$service->update(1, realpath(__DIR__ . '/RoleServiceTestData/UpdateThrowsWhenDescriptionIsNotSpecified.json'));
	}

	public function testUpdateThrowsWhenPermissionsAreNotSpecified() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_ROLE])
				)
		);

		$roleModel = $this->createStub(RoleModel::class);

		$service = new RoleService(
			$session,
			$roleModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(RoleService::ERROR_PERMISSIONS_ARE_REQUIRED);
		$service->update(1, realpath(__DIR__ . '/RoleServiceTestData/UpdateThrowsWhenPermissionsAreNotSpecified.json'));
	}

	public function testUpdateThrowsWhenPermissionsAreNotList() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_ROLE])
				)
		);

		$roleModel = $this->createStub(RoleModel::class);

		$service = new RoleService(
			$session,
			$roleModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(RoleService::ERROR_PERMISSIONS_ARE_INVALID);
		$service->update(1, realpath(__DIR__ . '/RoleServiceTestData/UpdateThrowsWhenPermissionsAreNotList.json'));
	}

	public function testUpdateThrowsWhenPermissionsAreInvalidType() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_ROLE])
				)
		);

		$roleModel = $this->createStub(RoleModel::class);

		$service = new RoleService(
			$session,
			$roleModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(RoleService::ERROR_PERMISSIONS_ARE_INVALID);
		$service->update(1, realpath(__DIR__ . '/RoleServiceTestData/UpdateThrowsWhenPermissionsAreInvalidType.json'));
	}

	public function testUpdateThrowsWhenPermissionsAreInvalid() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_ROLE])
				)
		);

		$roleModel = $this->createStub(RoleModel::class);

		$service = new RoleService(
			$session,
			$roleModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(RoleService::ERROR_PERMISSIONS_ARE_INVALID);
		$service->update(1, realpath(__DIR__ . '/RoleServiceTestData/UpdateThrowsWhenPermissionsAreInvalid.json'));
	}

	public function testUpdateThrowsWhenNotFound() {
		$id = 12;
		$name = 'Limited User'; // the sanitized name from the input file
		$description = 'Important People'; // the sanitized description from the input file
		$permissions = [
			Permission::LIST_OWN_EQUIPMENT_AUTHORIZATIONS,
			Permission::LIST_OWN_CARDS,
			Permission::LIST_OWN_CHARGES,
			Permission::LIST_OWN_PAYMENTS,
			Permission::READ_OWN_USER,
		];

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_ROLE])
				)
		);

		$roleModel = $this->createMock(RoleModel::class);
		$roleModel->expects($this->once())->method('update')->with(
			$this->callback(
				fn(Role $role) =>
					$role->id() === $id
					&& $role->name() === $name
					&& $role->description() === $description
					&& $role->permissions() === $permissions
					&& $role->is_system_role() === false
			)
		)
		->willReturn(null);

		$service = new RoleService(
			$session,
			$roleModel
		);

		self::expectException(NotFoundException::class);
		self::expectExceptionMessage(RoleService::ERROR_ROLE_NOT_FOUND);
		$service->update($id, realpath(__DIR__ . '/RoleServiceTestData/UpdateSuccess.json'));
	}

	public function testUpdateSuccess() {
		$id = 12;
		$name = 'Limited User'; // the sanitized name from the input file
		$description = 'Important People'; // the sanitized description from the input file
		$permissions = [
			Permission::LIST_OWN_EQUIPMENT_AUTHORIZATIONS,
			Permission::LIST_OWN_CARDS,
			Permission::LIST_OWN_CHARGES,
			Permission::LIST_OWN_PAYMENTS,
			Permission::READ_OWN_USER,
		];

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_ROLE])
				)
		);

		$roleModel = $this->createMock(RoleModel::class);
		$roleModel->expects($this->once())->method('update')->with(
			$this->callback(
				fn(Role $role) =>
					$role->id() === $id
					&& $role->name() === $name
					&& $role->description() === $description
					&& $role->permissions() === $permissions
					&& $role->is_system_role() === false
			)
		)
		->willReturnArgument(0);

		$service = new RoleService(
			$session,
			$roleModel
		);

		$role = $service->update($id, realpath(__DIR__ . '/RoleServiceTestData/UpdateSuccess.json'));

		self::assertInstanceOf(Role::class, $role);
		self::assertSame($id, $role->id());
		self::assertSame($name, $role->name());
		self::assertSame($description, $role->description());
		self::assertSame($permissions, $role->permissions());
		self::assertFalse($role->is_system_role());
	}

	#endregion test update()
}
