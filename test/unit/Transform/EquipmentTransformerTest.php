<?php

declare(strict_types=1);

namespace Test\Portalbox\Transform;

use PHPUnit\Framework\TestCase;
use Portalbox\Transform\EquipmentTransformer;
use Portalbox\Type\Equipment;
use Portalbox\Type\EquipmentType;
use Portalbox\Type\Location;

final class EquipmentTransformerTest extends TestCase {
	public function testSerialize(): void {
		$transformer = new EquipmentTransformer();

		$id = 42;
		$name = 'ClayPrint30';
		$mac_address = 'abcdef098765';
		$timeout = 240;
		$in_service = true;
		$in_use = false;
		$type_id = 23;
		$type_name = 'Clay Printer';
		$location_id = 2;
		$location_name = 'Sculpture Studio';

		$type = (new EquipmentType())
			->set_id($type_id)
			->set_name($type_name);

		$location = (new Location())
			->set_id($location_id)
			->set_name($location_name);

		$equipment = (new Equipment())
			->set_id($id)
			->set_name($name)
			->set_type($type)
			->set_location($location)
			->set_mac_address($mac_address)
			->set_timeout($timeout)
			->set_is_in_service($in_service)
			->set_is_in_use($in_use);

		$data = $transformer->serialize($equipment);

		self::assertNotNull($data);
		self::assertArrayHasKey('id', $data);
		self::assertEquals($id, $data['id']);
		self::assertArrayHasKey('name', $data);
		self::assertEquals($name, $data['name']);
		self::assertArrayNotHasKey('type_id', $data);
		self::assertArrayHasKey('type', $data);
		self::assertEquals($type_name, $data['type']);
		self::assertArrayNotHasKey('location_id', $data);
		self::assertArrayHasKey('location', $data);
		self::assertEquals($location_name, $data['location']);
		self::assertArrayHasKey('mac_address', $data);
		self::assertEquals($mac_address, $data['mac_address']);
		self::assertArrayHasKey('timeout', $data);
		self::assertEquals($timeout, $data['timeout']);
		self::assertArrayHasKey('in_service', $data);
		self::assertEquals($in_service, $data['in_service']);
		self::assertArrayHasKey('in_use', $data);
		self::assertEquals($in_use, $data['in_use']);

		$data = $transformer->serialize($equipment, true);

		self::assertNotNull($data);
		self::assertArrayHasKey('id', $data);
		self::assertEquals($id, $data['id']);
		self::assertArrayHasKey('name', $data);
		self::assertEquals($name, $data['name']);
		self::assertArrayHasKey('type_id', $data);
		self::assertEquals($type_id, $data['type_id']);
		self::assertArrayHasKey('type', $data);
		self::assertEquals($type_name, $data['type']);
		self::assertArrayHasKey('location_id', $data);
		self::assertEquals($location_id, $data['location_id']);
		self::assertArrayHasKey('location', $data);
		self::assertEquals($location_name, $data['location']);
		self::assertArrayHasKey('mac_address', $data);
		self::assertEquals($mac_address, $data['mac_address']);
		self::assertArrayHasKey('timeout', $data);
		self::assertEquals($timeout, $data['timeout']);
		self::assertArrayHasKey('in_service', $data);
		self::assertEquals($in_service, $data['in_service']);
		self::assertArrayHasKey('in_use', $data);
		self::assertEquals($in_use, $data['in_use']);
	}
}
