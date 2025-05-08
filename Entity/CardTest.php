<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Portalbox\Entity\CardType;
use Portalbox\Entity\ProxyCard;
use Portalbox\Entity\ShutdownCard;
use Portalbox\Entity\TrainingCard;
use Portalbox\Entity\UserCard;

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