<?php

declare(strict_types=1);

namespace Test\Portalbox\Type;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Portalbox\Enumeration\Permission;
use Portalbox\Type\APIKey;

final class APIKeyTest extends TestCase {
	public function testAgreement(): void {
		$id = 42;
		$name = 'Google App Suite Integration';
		$token = 'ABCDEF01234567890123456789ABCDEF';
		$permissions = [
			Permission::READ_API_KEY,
			Permission::READ_CARD
		];

		$key = (new APIKey())
			->set_id($id)
			->set_name($name)
			->set_token($token)
			->set_permissions($permissions);

		self::assertEquals($id, $key->id());
		self::assertEquals($name, $key->name());
		self::assertEquals($token, $key->token());
		self::assertEqualsCanonicalizing($permissions, $key->permissions());
	}

	public function testExceptionThrownOnInvalidName(): void {
		self::expectException(InvalidArgumentException::class);
		(new APIKey())->set_name('');
	}

	public function testSetInvalidPermissionListTriggersException(): void {
		$permissions = [
			Permission::READ_API_KEY,
			Permission::READ_CARD,
			-1
		];

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(APIKey::ERROR_INVALID_PERMISSION);
		$key = (new APIKey())->set_permissions($permissions);
	}
}
