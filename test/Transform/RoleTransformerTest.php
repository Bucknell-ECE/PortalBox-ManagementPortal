<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use PortalBox\Entity\Permission;
use PortalBox\Entity\Role;
use Portalbox\Transform\RoleTransformer;

final class RoleTransformerTest extends TestCase {
	public function testDeserialize(): void {
		$transformer = new RoleTransformer();

		$id = 42;
		$name = 'admin';
		$is_system_role = TRUE;
		$description = 'Users with this role have no restrictions.';
		$permissions = [
			Permission::LIST_OWN_EQUIPMENT_AUTHORIZATIONS,
			Permission::LIST_OWN_CARDS
		];

		$data = [
			'id' => $id,
			'name' => $name,
			'is_system_role' => $is_system_role,
			'description' => $description,
			'permissions' => $permissions
		];

		$role = $transformer->deserialize($data);

		self::assertNotNull($role);
		self::assertNull($role->id());
		self::assertEquals($name, $role->name());
		self::assertFalse($role->is_system_role());
		self::assertEquals($description, $role->description());
		self::assertIsArray($role->permissions());
		self::assertNotEmpty($role->permissions());
		self::assertContainsOnly('int', $role->permissions());
	}
}