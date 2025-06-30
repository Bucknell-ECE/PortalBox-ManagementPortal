<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Portalbox\Entity\EquipmentType;
use Portalbox\Entity\Role;
use Portalbox\Entity\User;
use Portalbox\Exception\NotFoundException;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Model\RoleModel;
use Portalbox\Model\UserModel;
use Portalbox\Service\UserService;

final class UserServiceTest extends TestCase {
	#region test import()

	public function testImportThrowsWhenLineTooShort() {
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$roleModel = $this->createStub(RoleModel::class);
		$roleModel->method('search')->willReturn([
			(new Role())->set_name('admin')
		]);

		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_INVALID_CSV_RECORD_LENGTH);
		$service->import(realpath(__DIR__ . '/data/ImportThrowsWhenLineTooShort.csv'));
	}

	public function testImportThrowsWhenLineTooLong() {
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$roleModel = $this->createStub(RoleModel::class);
		$roleModel->method('search')->willReturn([
			(new Role())->set_name('admin')
		]);

		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_INVALID_CSV_RECORD_LENGTH);
		$service->import(realpath(__DIR__ . '/data/ImportThrowsWhenLineTooLong.csv'));
	}

	public function testImportThrowsWhenRoleDoesNotExist() {
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$roleModel = $this->createStub(RoleModel::class);
		$roleModel->method('search')->willReturn([
			(new Role())->set_name('user')
		]);

		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_INVALID_CSV_ROLE);
		$service->import(realpath(__DIR__ . '/data/ImportThrowsWhenRoleDoesNotExist.csv'));
	}

	public function testImportThrowsWhenEmailIsInvalid() {
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$roleModel = $this->createStub(RoleModel::class);
		$roleModel->method('search')->willReturn([
			(new Role())->set_name('admin')
		]);

		$userModel = $this->createStub(UserModel::class);

		$service = new UserService(
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

	public function testPatchThrowsWhenUserDoesNotExist() {
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);

		$userModel = $this->createStub(UserModel::class);
		$userModel->method('read')->willReturn(null);

		$service = new UserService(
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(NotFoundException::class);
		self::expectExceptionMessage(UserService::ERROR_USER_NOT_FOUND);
		$service->patch(1, '');
	}

	public function testPatchThrowsWhenFileIsNotReadable() {
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);

		$userModel = $this->createStub(UserModel::class);
		$userModel->method('read')->willReturn(new User());

		$service = new UserService(
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
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);

		$userModel = $this->createStub(UserModel::class);
		$userModel->method('read')->willReturn(new User());

		$service = new UserService(
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_INVALID_PATCH);
		$service->patch(1, realpath(__DIR__ . '/data/PatchThrowsWhenDataIsNotArray.json'));
	}

	public function testPatchThrowsWhenPatchIncludesUnsupportedProperty() {
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);

		$userModel = $this->createStub(UserModel::class);
		$userModel->method('read')->willReturn(new User());

		$service = new UserService(
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_INVALID_PATCH);
		$service->patch(1, realpath(__DIR__ . '/data/PatchThrowsWhenPatchIncludesUnsupportedProperty.json'));
	}

	#region test patch(authorizations)

	public function testPatchAuthorizationThrowsWhenAuthorizationsNotArray() {
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);

		$userModel = $this->createStub(UserModel::class);
		$userModel->method('read')->willReturn(new User());

		$service = new UserService(
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_INVALID_AUTHORIZATIONS);
		$service->patch(1, realpath(__DIR__ . '/data/PatchAuthorizationThrowsWhenAuthorizationsNotArray.json'));
	}

	public function testPatchAuthorizationThrowsWhenAuthorizationIsNotInt() {
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$roleModel = $this->createStub(RoleModel::class);

		$userModel = $this->createStub(UserModel::class);
		$userModel->method('read')->willReturn(new User());

		$service = new UserService(
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_INVALID_AUTHORIZATIONS);
		$service->patch(1, realpath(__DIR__ . '/data/PatchAuthorizationThrowsWhenAuthorizationIsNotInt.json'));
	}

	public function testPatchAuthorizationThrowsWhenEquipmentTypeDoesNotExist() {
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('read')->willReturn(null);

		$roleModel = $this->createStub(RoleModel::class);

		$userModel = $this->createStub(UserModel::class);
		$userModel->method('read')->willReturn(new User());

		$service = new UserService(
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(UserService::ERROR_INVALID_AUTHORIZATIONS);
		$service->patch(1, realpath(__DIR__ . '/data/PatchAuthorizationThrowsWhenEquipmentTypeDoesNotExist.json'));
	}

	public function testPatchAuthorizationSuccess() {
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
			$equipmentTypeModel,
			$roleModel,
			$userModel
		);

		$user = $service->patch(1, realpath(__DIR__ . '/data/PatchAuthorizationSuccess.json'));
		self::assertInstanceOf(User::class, $user);
		self::assertSame([9, 10], $user->authorizations());
	}

	#endregion test patch(authorizations)

	#endregion test patch()
}