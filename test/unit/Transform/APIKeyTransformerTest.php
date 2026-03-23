<?php

declare(strict_types=1);

namespace Test\Portalbox\Transform;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Portalbox\Enumeration\Permission;
use Portalbox\Transform\APIKeyTransformer;
use Portalbox\Type\APIKey;

final class APIKeyTransformerTest extends TestCase {
	public function testSerialize(): void {
		$transformer = new APIKeyTransformer();

		$id = 42;
		$name = 'laser scalpel';
		$token = '56789-ABCDEF-1234-1234';
		$permissions = [
			Permission::LIST_USERS,
			Permission::READ_USER
		];

		$key = (new APIKey())
			->set_id($id)
			->set_name($name)
			->set_token($token);

		$data = $transformer->serialize($key);

		self::assertNotNull($data);
		self::assertArrayHasKey('id', $data);
		self::assertEquals($id, $data['id']);
		self::assertArrayHasKey('name', $data);
		self::assertEquals($name, $data['name']);
		self::assertArrayHasKey('token', $data);
		self::assertEquals($token, $data['token']);
		self::assertArrayNotHasKey('permissions', $data);

		$data = $transformer->serialize($key, true);

		self::assertNotNull($data);
		self::assertArrayHasKey('id', $data);
		self::assertEquals($id, $data['id']);
		self::assertArrayHasKey('name', $data);
		self::assertEquals($name, $data['name']);
		self::assertArrayHasKey('token', $data);
		self::assertEquals($token, $data['token']);
		self::assertArrayHasKey('permissions', $data);
		self::assertIsArray($data['permissions']);
		self::assertSame(count($permissions), count($data['permissions']));
		self::assertContains(Permission::LIST_USERS->value, $data['permissions']);
		self::assertContains(Permission::READ_USER->value, $data['permissions']);
	}
}
