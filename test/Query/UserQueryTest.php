<?php

declare(strict_types=1);

namespace Test\Portalbox\Query;

use PHPUnit\Framework\TestCase;
use Portalbox\Query\UserQuery;

final class UserQueryTest extends TestCase {
	public function testAgreement(): void {
		$email = 'sebastian@tomegan.tech';
		$name = 'Sebastian';
		$comment = 'skilled crafter';
		$equipment_id = 6;
		$include_inactive = false;
		$role_id = 2;

		$query = new UserQuery();

		self::assertNull($query->email());
		self::assertNull($query->name());
		self::assertNull($query->comment());
		self::assertNull($query->equipment_id());
		self::assertNull($query->include_inactive());
		self::assertNull($query->role_id());

		$query
			->set_email($email)
			->set_name($name)
			->set_comment($comment)
			->set_equipment_id($equipment_id)
			->set_include_inactive($include_inactive)
			->set_role_id($role_id);

		self::assertSame($email, $query->email());
		self::assertSame($name, $query->name());
		self::assertSame($comment, $query->comment());
		self::assertSame($equipment_id, $query->equipment_id());
		self::assertSame($include_inactive, $query->include_inactive());
		self::assertSame($role_id, $query->role_id());
	}
}
