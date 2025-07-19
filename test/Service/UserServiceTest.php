<?php

declare(strict_types=1);

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
use Portalbox\Service\UserService;
use Portalbox\Session\SessionInterface;

final class UserServiceTest extends TestCase {
	#region test import()

	public function testImportThrowsWhenLineTooShort() {
		$session = $this->createStub(SessionInterface::class);
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
		$service->import(realpath(__DIR__ . '/data/ImportThrowsWhenLineTooShort.csv'));
	}

	public function testImportThrowsWhenLineTooLong() {
		$session = $this->createStub(SessionInterface::class);
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
		$service->import(realpath(__DIR__ . '/data/ImportThrowsWhenLineTooLong.csv'));
	}

	public function testImportThrowsWhenRoleDoesNotExist() {
		$session = $this->createStub(SessionInterface::class);
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
		$service->import(realpath(__DIR__ . '/data/ImportThrowsWhenRoleDoesNotExist.csv'));
	}

	public function testImportThrowsWhenEmailIsInvalid() {
		$session = $this->createStub(SessionInterface::class);
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
		$service->import(realpath(__DIR__ . '/data/ImportThrowsWhenEmailIsInvalid.csv'));
	}

	public function testImportSuccess() {
		$role = (new Role())->set_id(3)->set_name('admin');

		$session = $this->createStub(SessionInterface::class);
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

		$users = $service->import(realpath(__DIR__ . '/data/ImportSuccess.csv'));
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
		// PHP warning is intentionally suppressed n next line for testing
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
		$service->patch(1, realpath(__DIR__ . '/data/PatchThrowsWhenDataIsNotArray.json'));
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
		$service->patch(1, realpath(__DIR__ . '/data/PatchThrowsWhenPatchIncludesUnsupportedProperty.json'));
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
		$service->patch(1, realpath(__DIR__ . '/data/PatchAuthorizationSuccess.json'));
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
		$service->patch(1, realpath(__DIR__ . '/data/PatchAuthorizationThrowsWhenAuthorizationsNotArray.json'));
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
		$service->patch(1, realpath(__DIR__ . '/data/PatchAuthorizationThrowsWhenAuthorizationIsNotInt.json'));
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
		$service->patch(1, realpath(__DIR__ . '/data/PatchAuthorizationThrowsWhenEquipmentTypeDoesNotExist.json'));
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

		$user = $service->patch(1, realpath(__DIR__ . '/data/PatchAuthorizationSuccess.json'));
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
		$service->patch(1, realpath(__DIR__ . '/data/PatchPINSuccess.json'));
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
		$service->patch(1, realpath(__DIR__ . '/data/PatchPINThrowsWhenNotString.json'));
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
		$service->patch(1, realpath(__DIR__ . '/data/PatchPINThrowsWhenNotFourDigits.json'));
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

		$user = $service->patch(1, realpath(__DIR__ . '/data/PatchPINSuccess.json'));
		self::assertInstanceOf(User::class, $user);
		self::assertTrue(password_verify('1234', $user->pin()));
	}

	#endregion test patch(pin)

	#endregion test patch()
}