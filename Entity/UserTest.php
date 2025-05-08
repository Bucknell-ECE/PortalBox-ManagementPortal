<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Portalbox\Entity\User;

final class UserTest extends TestCase {
	public function testAgreement(): void {
		$id = 42;
		$name = 'Tom Egan';
		$email = 'tom@tomegan.tech';
		$comment = 'Test Monkey';
		$active = FALSE;
		$authorizations = array(
			34,
			23
		);
		$num_authorizations = count($authorizations);

		$user = (new User())
			->set_id($id)
			->set_name($name)
			->set_email($email)
			->set_comment($comment)
			->set_is_active($active)
			->set_authorizations($authorizations);

		self::assertEquals($id, $user->id());
		self::assertEquals($name, $user->name());
		self::assertEquals($email, $user->email());
		self::assertEquals($comment, $user->comment());
		self::assertEquals($active, $user->is_active());
		self::assertIsIterable($user->authorizations());
		self::assertCount($num_authorizations, $user->authorizations());
		self::assertContains(34, $user->authorizations());
		self::assertContains(23, $user->authorizations());
	}
}