<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Portalbox\Entity\ChargePolicy;
use Portalbox\Entity\Equipment;
use Portalbox\Entity\EquipmentType;
use Portalbox\Entity\Location;

final class EquipmentTest extends TestCase {
	public function testAgreement(): void {
		$type_id = 7;
		$location_id = 6;
		$id = 42;
		$name = 'Roland Cutmaster 1000';
		$timeout = 0;
		$is_in_service = true;
		$is_in_use = false;
		$service_minutes = 500;

		// nullables
		$mac_address = '0123456789ab';
		$ip_address = '172.0.0.1';

		$equipment = (new Equipment())
			->set_id($id)
			->set_name($name)
			->set_type_id($type_id)
			->set_location_id($location_id)
			->set_timeout($timeout)
			->set_is_in_service($is_in_service)
			->set_is_in_use($is_in_use)
			->set_service_minutes($service_minutes);

		self::assertEquals($id, $equipment->id());
		self::assertEquals($name, $equipment->name());
		self::assertEquals($type_id, $equipment->type_id());
		self::assertNull($equipment->type());
		self::assertEquals($location_id, $equipment->location_id());
		self::assertNull($equipment->location());
		self::assertNull($equipment->mac_address());
		self::assertEquals($timeout, $equipment->timeout());
		self::assertEquals($is_in_service, $equipment->is_in_service());
		self::assertEquals($is_in_use, $equipment->is_in_use());
		self::assertEquals($service_minutes, $equipment->service_minutes());
		self::assertNull($equipment->ip_address());

		$equipment
			->set_ip_address($ip_address)
			->set_mac_address($mac_address);
		self::assertEquals($mac_address, $equipment->mac_address());
		self::assertEquals($ip_address, $equipment->ip_address());
	}

	public function testJoinedDataAgreement(): void {
		$type_id = 7;
		$type_name = 'Vinyl Cutter';
		$requires_training = true;
		$charge_policy_id = ChargePolicy::NO_CHARGE;

		$type = (new EquipmentType())
			->set_id($type_id)
			->set_name($type_name)
			->set_requires_training($requires_training)
			->set_charge_policy_id($charge_policy_id);

		$location_id = 6;
		$location_name = 'Sign Shop';

		$location = (new Location())
			->set_id($location_id)
			->set_name($location_name);

		$equipment = (new Equipment())
			->set_type($type)
			->set_location($location);

		self::assertEquals($type_id, $equipment->type_id());
		self::assertEquals($type, $equipment->type());
		self::assertEquals($location_id, $equipment->location_id());
		self::assertEquals($location, $equipment->location());
	}

	public function testThrowsExceptionOnInvalidName():void {
		self::expectException(InvalidArgumentException::class);
		(new Equipment())->set_name('');
	}

	public function testThrowsExceptionOnInvalidMACAddress():void {
		self::expectException(InvalidArgumentException::class);
		(new Equipment())->set_mac_address('OO:OO:OO:OO:OO:OO'); // Capital O not 0
	}

	public function testMACAddressNormalization(): void {
		$mac_address = '01-23-45-67-89-AB';

		$equipment = (new Equipment())
			->set_mac_address($mac_address);

		self::assertEquals('0123456789ab', $equipment->mac_address());
	}
}
