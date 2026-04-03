<?php

declare(strict_types=1);

namespace Test\Portalbox\Query;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Portalbox\Query\ChargeQuery;

final class ChargeQueryTest extends TestCase {
	public function testAgreement(): void {
		$user_id = 42;
		$on_or_before = new DateTimeImmutable('2020-05-23 00:00:00');
		$on_or_after = new DateTimeImmutable('2020-05-23 12:00:00');
		$equipment_id = 137;

		$query = new ChargeQuery();

		self::assertNull($query->user_id());
		self::assertNull($query->on_or_before());
		self::assertNull($query->on_or_after());
		self::assertNull($query->equipment_id());

		$query->set_user_id($user_id);
		$query->set_on_or_before($on_or_before);
		$query->set_on_or_after($on_or_after);
		$query->set_equipment_id($equipment_id);

		self::assertSame($user_id, $query->user_id());
		self::assertSame($on_or_before, $query->on_or_before());
		self::assertSame($on_or_after, $query->on_or_after());
		self::assertSame($equipment_id, $query->equipment_id());
	}
}
