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

		$id = 42;
		$name = 'Roland Cutmaster 1000';
		$mac_address = '0123456789ab';
		$timeout = 0;
		$is_in_service = true;
		$is_in_use = false;
		$service_minutes = 500;

		$equipment = (new Equipment())
			->set_id($id)
			->set_name($name)
			->set_type($type)
			->set_location($location)
			->set_mac_address($mac_address)
			->set_timeout($timeout)
			->set_is_in_service($is_in_service)
			->set_is_in_use($is_in_use)
			->set_service_minutes($service_minutes);

		self::assertEquals($id, $equipment->id());
		self::assertEquals($name, $equipment->name());
		self::assertEquals($type_id, $equipment->type_id());
		self::assertEquals($type_name, $equipment->type()->name());
		self::assertEquals($location_id, $equipment->location_id());
		self::assertEquals($location_name, $equipment->location()->name());
		self::assertEquals($mac_address, $equipment->mac_address());
		self::assertEquals($timeout, $equipment->timeout());
		self::assertEquals($is_in_service, $equipment->is_in_service());
		self::assertEquals($is_in_use, $equipment->is_in_use());
		self::assertEquals($service_minutes, $equipment->service_minutes());
	}
}