<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Portalbox\Entity\APIKey;

final class APIKeyTest extends TestCase {
	public function testAgreement(): void {
		$id = 42;
		$name = 'Google App Suite Integration';
		$token = 'ABCDEF01234567890123456789ABCDEF';

		$key = (new APIKey())
			->set_id($id)
			->set_name($name)
			->set_token($token);

		self::assertEquals($id, $key->id());
		self::assertEquals($name, $key->name());
		self::assertEquals($token, $key->token());
	}
}