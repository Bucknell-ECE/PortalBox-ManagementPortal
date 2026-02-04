<?php

declare(strict_types=1);

namespace Test\Portalbox\Transform;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use PortalBox\Enumeration\Permission;
use Portalbox\Transform\RoleTransformer;
use PortalBox\Type\Role;

final class RoleTransformerTest extends TestCase {
	public function testSerialize(): void {
		$transformer = new RoleTransformer();

		$id = 42;
		$name = 'admin';
		$is_system_role = true;
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
		self::assertContains(Permission::LIST_OWN_EQUIPMENT_AUTHORIZATIONS->value, $data['permissions']);
		self::assertContains(Permission::LIST_OWN_CARDS->value, $data['permissions']);
	}
}
