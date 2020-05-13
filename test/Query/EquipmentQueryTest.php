<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Portalbox\Query\EquipmentQuery;

final class EquipmentQueryTest extends TestCase {
	public function testAgreement(): void {
		$type = 'Hyperspanner';
		$location = 'Space Dock';
		$location_id = 99;
		$include_out_of_service = true;

		$query = new EquipmentQuery();

		self::assertFalse($query->include_out_of_service());
		self::assertNull($query->location());
		self::assertNull($query->location_id());
		self::assertNull($query->type());
		
		$query->set_include_out_of_service($include_out_of_service);
		$query->set_location($location);
		$query->set_location_id($location_id);
		$query->set_type($type);

		self::assertEquals($include_out_of_service, $query->include_out_of_service());
		self::assertEquals($location, $query->location());
		self::assertEquals($location_id, $query->location_id());
		self::assertEquals($type, $query->type());
	}
}