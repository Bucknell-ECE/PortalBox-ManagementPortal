<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Portalbox\Entity\Role;

final class RoleTest extends TestCase {
	public function testAgreement(): void {
		$id = 42;
		$name = 'admin';
		$is_system_role = TRUE;
		$description = 'Users with this role have no restrictions.';

		$role = (new Role())
			->set_id($id)
			->set_name($name)
			->set_is_system_role($is_system_role)
			->set_description($description);

		self::assertEquals($id, $role->id());
		self::assertEquals($name, $role->name());
		self::assertEquals($is_system_role, $role->is_system_role());
		self::assertEquals($description, $role->description());
	}
}