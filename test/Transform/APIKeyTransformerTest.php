<?php

declare(strict_types=1);

namespace Test\Portalbox\Transform;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Portalbox\Transform\APIKeyTransformer;
use PortalBox\Type\APIKey;

final class APIKeyTransformerTest extends TestCase {
	public function testSerialize(): void {
		$transformer = new APIKeyTransformer();

		$id = 42;
		$name = 'laser scalpel';
		$token = '56789-ABCDEF-1234-1234';

		$key = (new APIKey())
			->set_id($id)
			->set_name($name)
			->set_token($token);

		$data = $transformer->serialize($key, true);

		self::assertNotNull($data);
		self::assertArrayHasKey('id', $data);
		self::assertEquals($id, $data['id']);
		self::assertArrayHasKey('name', $data);
		self::assertEquals($name, $data['name']);
		self::assertArrayHasKey('token', $data);
		self::assertEquals($token, $data['token']);
	}
}
