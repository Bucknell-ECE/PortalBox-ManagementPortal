<?php

declare(strict_types=1);

namespace Test\Portalbox\Entity;

use PHPUnit\Framework\TestCase;
use Portalbox\Entity\User;
use Portalbox\Model\UserModel;
use Portalbox\Model\Entity\UserCard;

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
