<?php

declare(strict_types=1);

namespace Test\Portalbox\Query;

use PHPUnit\Framework\TestCase;
use Portalbox\Enumeration\LoggedEventType;
use Portalbox\Query\LoggedEventQuery;

final class LoggedEventQueryTest extends TestCase {
	public function testAgreement(): void {
		$on_or_before = '2020-05-12';
		$on_or_after = '2020-05-11';
		$equipment_id = 99;
		$location_id = 11;
		$type = LoggedEventType::TRAINING;

		$query = new LoggedEventQuery();

		self::assertNull($query->on_or_before());
		self::assertNull($query->on_or_after());
		self::assertNull($query->equipment_id());
		self::assertNull($query->location_id());
		self::assertNull($query->type());
		
		
		$query->set_on_or_before($on_or_before);
		$query->set_on_or_after($on_or_after);
		$query->set_equipment_id($equipment_id);
		$query->set_location_id($location_id);
		$query->set_type($type);

		self::assertSame($on_or_before, $query->on_or_before());
		self::assertSame($on_or_after, $query->on_or_after());
		self::assertSame($equipment_id, $query->equipment_id());
		self::assertSame($location_id, $query->location_id());
		self::assertSame($type, $query->type());
	}
}
