<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Portalbox\Entity\Permission;
use Portalbox\Entity\Role;

final class RoleTest extends TestCase {
	public function testAgreement(): void {
		$id = 42;
		$name = 'admin';
		$is_system_role = TRUE;
		$description = 'Users with this role have no restrictions.';
		$permissions = array(
			Permission::READ_API_KEY,
			Permission::READ_CARD
		);
		$permissions_count = 2;

		$role = (new Role())
			->set_id($id)
			->set_name($name)
			->set_is_system_role($is_system_role)
			->set_description($description)
			->set_permissions($permissions);

		self::assertEquals($id, $role->id());
		self::assertEquals($name, $role->name());
		self::assertEquals($is_system_role, $role->is_system_role());
		self::assertEquals($description, $role->description());
		self::assertIsIterable($role->permissions());
		self::assertCount($permissions_count, $role->permissions());
		self::assertContains(Permission::READ_API_KEY, $role->permissions());
		self::assertContains(Permission::READ_CARD, $role->permissions());
	}

	public function testSetInvalidPermissionListTriggersException(): void {
		$permissions = array(
			Permission::READ_API_KEY,
			Permission::READ_CARD,
			-1
		);

		$this->expectException(InvalidArgumentException::class);

		$role = (new Role())->set_permissions($permissions);
	}
}