<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Portalbox\Entity\Role;
use Portalbox\Entity\User;
use Portalbox\Model\RoleModel;
use Portalbox\Model\UserModel;
use Portalbox\Service\UserService;

final class UserServiceTest extends TestCase {
    public function testImportThrowsWhenLineTooShort() {
        $roleModel = $this->createStub(RoleModel::class);
        $roleModel->method('search')->willReturn([
            (new Role())->set_name('admin')
        ]);

        $userModel = $this->createStub(UserModel::class);

        $service = new UserService(
            $roleModel,
            $userModel
        );

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(UserService::ERROR_INVALID_CSV_RECORD_LENGTH);
        $service->import(realpath(__DIR__ . '/data/ImportThrowsWhenLineTooShort.csv'));
    }

    public function testImportThrowsWhenLineTooLong() {
        $roleModel = $this->createStub(RoleModel::class);
        $roleModel->method('search')->willReturn([
            (new Role())->set_name('admin')
        ]);

        $userModel = $this->createStub(UserModel::class);

        $service = new UserService(
            $roleModel,
            $userModel
        );

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(UserService::ERROR_INVALID_CSV_RECORD_LENGTH);
        $service->import(realpath(__DIR__ . '/data/ImportThrowsWhenLineTooLong.csv'));
    }

    public function testImportThrowsWhenRoleDoesNotExist() {
        $roleModel = $this->createStub(RoleModel::class);
        $roleModel->method('search')->willReturn([
            (new Role())->set_name('user')
        ]);

        $userModel = $this->createStub(UserModel::class);

        $service = new UserService(
            $roleModel,
            $userModel
        );

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(UserService::ERROR_INVALID_CSV_ROLE);
        $service->import(realpath(__DIR__ . '/data/ImportThrowsWhenRoleDoesNotExist.csv'));
    }

    public function testImportThrowsWhenEmailIsInvalid() {
        $roleModel = $this->createStub(RoleModel::class);
        $roleModel->method('search')->willReturn([
            (new Role())->set_name('admin')
        ]);

        $userModel = $this->createStub(UserModel::class);

        $service = new UserService(
            $roleModel,
            $userModel
        );

        self::expectException(InvalidArgumentException::class);
        self::expectExceptionMessage(UserService::ERROR_INVALID_EMAIL);
        $service->import(realpath(__DIR__ . '/data/ImportThrowsWhenEmailIsInvalid.csv'));
    }

    public function testImportSuccess() {
        $role = (new Role())->set_id(3)->set_name('admin');

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
}