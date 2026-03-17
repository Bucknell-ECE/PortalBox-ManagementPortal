<?php

declare(strict_types=1);

namespace Test\Portalbox\Query;

use PHPUnit\Framework\TestCase;
use Portalbox\Query\EquipmentQuery;

final class EquipmentQueryTest extends TestCase {
	public function testAgreement(): void {
		$type = 'Hyperspanner';
		$mac_address = '00:11:22:CC:AA:BB';
		$normalized_mac_address = '001122ccaabb';
		$location = 'Space Dock';
		$location_id = 99;
		$exclude_out_of_service = true;

		$query = new EquipmentQuery();

		self::assertFalse($query->exclude_out_of_service());
		self::assertNull($query->mac_address());
		self::assertNull($query->location());
		self::assertNull($query->location_id());
		self::assertNull($query->type());

		$query
			->set_exclude_out_of_service($exclude_out_of_service)
			->set_mac_address($mac_address)
			->set_location($location)
			->set_location_id($location_id)
			->set_type($type);

		self::assertSame($exclude_out_of_service, $query->exclude_out_of_service());
		self::assertSame($normalized_mac_address, $query->mac_address());
		self::assertSame($location, $query->location());
		self::assertSame($location_id, $query->location_id());
		self::assertSame($type, $query->type());
	}
}
