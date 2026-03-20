<?php

declare(strict_types=1);

namespace Test\Portalbox\Query;

use PHPUnit\Framework\TestCase;
use Portalbox\Enumeration\CardType;
use Portalbox\Query\CardQuery;

final class CardQueryTest extends TestCase {
	public function testAgreement(): void {
		$id = 1234567890;
		$user_id = 42;
		$equipment_type_id = 13;
		$type = CardType::SHUTDOWN;

		$query = new CardQuery();

		self::assertNull($query->user_id());
		self::assertNull($query->equipment_type_id());
		self::assertNull($query->id());
		self::assertNull($query->type());

		$query
			->set_user_id($user_id)
			->set_equipment_type_id($equipment_type_id)
			->set_id($id)
			->set_type($type);

		self::assertSame($user_id, $query->user_id());
		self::assertSame($equipment_type_id, $query->equipment_type_id());
		self::assertSame($id, $query->id());
		self::assertSame($type, $query->type());
	}
}
