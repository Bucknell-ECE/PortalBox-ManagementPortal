<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Portalbox\Query\UserQuery;

final class UserQueryTest extends TestCase {
	public function testAgreement(): void {
		$email = 'sebastian@tomegan.tech';

		$query = (new UserQuery())
			->set_email($email);

		self::assertEquals($email, $query->email());
	}
}