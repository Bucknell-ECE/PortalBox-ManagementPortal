<?php

declare(strict_types=1);

namespace Test\Portalbox\Type;

use PHPUnit\Framework\TestCase;
use Portalbox\Type\CardType;
use Portalbox\Type\ProxyCard;
use Portalbox\Type\ShutdownCard;
use Portalbox\Type\TrainingCard;
use Portalbox\Type\UserCard;

final class CardTest extends TestCase {
	public function testAgreement(): void {
		$id = 987654321;
		$equipment_type_id = 42;
		$user_id = 34;

		$card = (new ShutdownCard())->set_id($id);

		self::assertEquals($id, $card->id());
		self::assertEquals(CardType::SHUTDOWN, $card->type_id());

		$card = (new ProxyCard())->set_id($id);

		self::assertEquals($id, $card->id());
		self::assertEquals(CardType::PROXY, $card->type_id());

		$card = (new TrainingCard())
			->set_id($id)
			->set_equipment_type_id($equipment_type_id);

		self::assertEquals($id, $card->id());
		self::assertEquals(CardType::TRAINING, $card->type_id());
		self::assertEquals($equipment_type_id, $card->equipment_type_id());

		$card = (new UserCard())
			->set_id($id)
			->set_user_id($user_id);

		self::assertEquals($id, $card->id());
		self::assertEquals(CardType::USER, $card->type_id());
		self::assertEquals($user_id, $card->user_id());
	}
}
