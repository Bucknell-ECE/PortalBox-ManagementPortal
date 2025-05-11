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

	public function testDeserializeInvalidDataName(): void {
		$transformer = new RoleTransformer();

		$id = 42;
		$is_system_role = TRUE;
		$description = 'Users with this role have no restrictions.';
		$permissions = [
			Permission::LIST_OWN_EQUIPMENT_AUTHORIZATIONS,
			Permission::LIST_OWN_CARDS
		];

		$data = [
			'id' => $id,
			'is_system_role' => $is_system_role,
			'description' => $description,
			'permissions' => $permissions
		];

		$this->expectException(InvalidArgumentException::class);
		$role = $transformer->deserialize($data);
	}

	public function testDeserializeInvalidDataDescription(): void {
		$transformer = new RoleTransformer();

		$id = 42;
		$name = 'admin';
		$is_system_role = TRUE;
		$permissions = [
			Permission::LIST_OWN_EQUIPMENT_AUTHORIZATIONS,
			Permission::LIST_OWN_CARDS
		];

		$data = [
			'id' => $id,
			'name' => $name,
			'is_system_role' => $is_system_role,
			'permissions' => $permissions
		];

		$this->expectException(InvalidArgumentException::class);
		$role = $transformer->deserialize($data);
	}

	public function testDeserializeInvalidDataPermissions(): void {
		$transformer = new RoleTransformer();

		$id = 42;
		$name = 'admin';
		$is_system_role = TRUE;
		$description = 'Users with this role have no restrictions.';

		$data = [
			'id' => $id,
			'name' => $name,
			'is_system_role' => $is_system_role,
			'description' => $description
		];

		$this->expectException(InvalidArgumentException::class);
		$role = $transformer->deserialize($data);
	}

	public function testSerialize(): void {
		$transformer = new RoleTransformer();

		$id = 42;
		$name = 'admin';
		$is_system_role = TRUE;
		$description = 'Users with this role have no restrictions.';
		$permissions = [
			Permission::LIST_OWN_EQUIPMENT_AUTHORIZATIONS,
			Permission::LIST_OWN_CARDS
		];

		$role = (new Role())
					->set_id($id)
					->set_name($name)
					->set_is_system_role($is_system_role)
					->set_description($description)
					->set_permissions($permissions);

		$data = $transformer->serialize($role, true);

		self::assertNotNull($data);
		self::assertArrayHasKey('id', $data);
		self::assertEquals($id, $data['id']);
		self::assertArrayHasKey('name', $data);
		self::assertEquals($name, $data['name']);
		self::assertArrayHasKey('system_role', $data);
		self::assertEquals($is_system_role, $data['system_role']);
		self::assertArrayHasKey('description', $data);
		self::assertEquals($description, $data['description']);
		self::assertArrayHasKey('permissions', $data);
		self::assertIsArray($data['permissions']);
		self::assertNotEmpty($data['permissions']);
		self::assertContains(Permission::LIST_OWN_EQUIPMENT_AUTHORIZATIONS, $data['permissions']);
		self::assertContains(Permission::LIST_OWN_CARDS, $data['permissions']);
	}
}