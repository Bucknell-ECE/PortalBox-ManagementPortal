<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Bucknell\Portalbox\Entity\User;

final class UserTest extends TestCase {
	public function testAgreement(): void {
		$id = 42;
		$name = 'Tom Egan';
		$email = 'tom@tomegan.tech';
		$comment = 'Test Monkey';
		$active = FALSE;

		$user = (new User())
			->set_id($id)
			->set_name($name)
			->set_email($email)
			->set_comment($comment)
			->set_is_active($active);

		self::assertEquals($id, $user->id());
		self::assertEquals($name, $user->name());
		self::assertEquals($email, $user->email());
		self::assertEquals($comment, $user->comment());
		self::assertEquals($active, $user->is_active());
	}
}