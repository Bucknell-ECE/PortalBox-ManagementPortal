<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Portalbox\Query\CardQuery;

final class CardQueryTest extends TestCase {
	public function testAgreement(): void {
		$user_id = 42;
		$equipment_type_id = 13;

		$query = new CardQuery();

		self::assertNull($query->user_id());
		self::assertNull($query->equipment_type_id());

		$query->set_user_id($user_id);
		$query->set_equipment_type_id($equipment_type_id);

		self::assertEquals($user_id, $query->user_id());
		self::assertEquals($equipment_type_id, $query->equipment_type_id());
	}
}