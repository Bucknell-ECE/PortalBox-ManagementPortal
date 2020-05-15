<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Portalbox\Query\LoggedEventQuery;

final class LoggedEventQueryTest extends TestCase {
	public function testAgreement(): void {
		$on_or_before = '2020-05-12';
		$on_or_after = '2020-05-11';
		$equipment_id = 99;
		$location_id = 11;
		$type_id = 2;

		$query = new LoggedEventQuery();

		self::assertNull($query->on_or_before());
		self::assertNull($query->on_or_after());
		self::assertNull($query->equipment_id());
		self::assertNull($query->location_id());
		self::assertNull($query->type_id());
		
		
		$query->set_on_or_before($on_or_before);
		$query->set_on_or_after($on_or_after);
		$query->set_equipment_id($equipment_id);
		$query->set_location_id($location_id);
		$query->set_type_id($type_id);

		self::assertEquals($on_or_before, $query->on_or_before());
		self::assertEquals($on_or_after, $query->on_or_after());
		self::assertEquals($equipment_id, $query->equipment_id());
		self::assertEquals($location_id, $query->location_id());
		self::assertEquals($type_id, $query->type_id());
	}
}