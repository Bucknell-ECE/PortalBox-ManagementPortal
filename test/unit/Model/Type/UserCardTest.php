<?php

declare(strict_types=1);

namespace Test\Portalbox\Type;

use PHPUnit\Framework\TestCase;
use Portalbox\Model\UserModel;
use Portalbox\Model\Type\UserCard;
use Portalbox\Type\User;

final class UserCardTest extends TestCase {
	public function testAgreement(): void {
		$user = new User();

		$model = $this->createStub(UserModel::class);
		$model->method('read')->willReturn($user);

		$card = (new UserCard($model))
			->set_user_id(1);

		self::assertSame($user, $card->user());
	}
}
