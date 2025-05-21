<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Portalbox\Query\APIKeyQuery;

final class APIKeyQueryTest extends TestCase {
	public function testAgreement(): void {
		$token = 'ABCDEF-4567-FEDC-BA98';

		$query = new APIKeyQuery();

		self::assertNull($query->token());
		
		$query->set_token($token);

		self::assertEquals($token, $query->token());
	}
}