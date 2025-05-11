<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Portalbox\Config;
use Portalbox\Entity\Permission;
use Portalbox\Entity\Role;
use Portalbox\Model\RoleModel;

final class RoleModelTest extends TestCase {
	public function testModel(): void {
		$model = new RoleModel(Config::config());

		$name = 'Test Role';
		$is_system_role = FALSE;
		$description = 'A pointless role';

		$role = (new Role())
			->set_name($name)
			->set_is_system_role($is_system_role)
			->set_description($description)
			->set_permissions(array(Permission::CREATE_API_KEY));

		$role_as_created = $model->create($role);

		$role_id = $role_as_created->id();
		self::assertIsInt($role_id);
		self::assertEquals($name, $role_as_created->name());
		self::assertEquals($is_system_role, $role_as_created->is_system_role());
		self::assertEquals($description, $role_as_created->description());
		self::assertTrue($role->has_permission(Permission::CREATE_API_KEY));

		$role_as_found = $model->read($role_id);

		self::assertNotNull($role_as_found);
		self::assertEquals($role_id, $role_as_found->id());
		self::assertEquals($name, $role_as_found->name());
		self::assertEquals($is_system_role, $role_as_found->is_system_role());
		self::assertEquals($description, $role_as_found->description());
		self::assertTrue($role->has_permission(Permission::CREATE_API_KEY));

		$roles_as_found = $model->search();
		self::assertIsArray($roles_as_found);
		self::assertNotEmpty($roles_as_found);
		self::assertContainsOnlyInstancesOf(Role::class, $roles_as_found);

		$role_as_deleted = $model->delete($role_id);

		self::assertNotNull($role_as_deleted);
		self::assertEquals($role_id, $role_as_deleted->id());
		self::assertEquals($name, $role_as_deleted->name());
		self::assertEquals($is_system_role, $role_as_deleted->is_system_role());
		self::assertEquals($description, $role_as_deleted->description());
		self::assertTrue($role->has_permission(Permission::CREATE_API_KEY));

		$role_as_not_found = $model->read($role_id);

		self::assertNull($role_as_not_found);
	}
}