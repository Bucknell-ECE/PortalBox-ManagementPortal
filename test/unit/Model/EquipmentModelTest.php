<?php

declare(strict_types=1);

namespace Test\Portalbox\Model;

use PHPUnit\Framework\TestCase;
use Portalbox\Config;
use Portalbox\Enumeration\ChargePolicy;
use Portalbox\Model\EquipmentModel;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Model\LocationModel;
use Portalbox\Query\EquipmentQuery;
use Portalbox\Type\Equipment;
use Portalbox\Type\EquipmentType;
use Portalbox\Type\Location;

final class EquipmentModelTest extends TestCase {
	/**
	 * A location that exists in the db
	 */
	private static Location $location;

	/**
	 * An equipment type which exists in the db
	 */
	private static EquipmentType $type;

	/**
	 * The configuration
	 */
	private static Config $config;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		self::$config = Config::config();

		// provision a location in the db
		$model = new LocationModel(self::$config);

		$name = 'Robotics Shop';

		$location = (new Location())
			->set_name($name);

		self::$location = $model->create($location);

		// provision an equipment type in the db
		$model = new EquipmentTypeModel(self::$config);

		$name = 'Floodlight';
		$requires_training = false;
		$charge_policy = ChargePolicy::NO_CHARGE;

		$type = (new EquipmentType())
			->set_name($name)
			->set_requires_training($requires_training)
			->set_charge_policy($charge_policy)
			->set_allow_proxy(false);

		self::$type = $model->create($type);
	}

	public static function tearDownAfterClass(): void {
		// deprovision a location in the db
		$model = new LocationModel(self::$config);
		$model->delete(self::$location->id());

		// deprovision an equipment type in the db
		$model = new EquipmentTypeModel(self::$config);
		$model->delete(self::$type->id());

		parent::tearDownAfterClass();
	}

	public function testCRUD(): void {
		$model = new EquipmentModel(self::$config);

		$name = '1000W Floodlight';
		$mac_address = '0123456789ab';
		$timeout = 0;
		$is_in_service = true;
		$service_minutes = 500;

		$equipment = (new Equipment())
			->set_name($name)
			->set_type(self::$type)
			->set_location(self::$location)
			->set_mac_address($mac_address)
			->set_timeout($timeout)
			->set_is_in_service($is_in_service)
			->set_service_minutes($service_minutes);

		$equipment_as_created = $model->create($equipment);

		$equipment_id = $equipment_as_created->id();
		self::assertIsInt($equipment_id);
		self::assertEquals($name, $equipment_as_created->name());
		self::assertEquals(self::$type->id(), $equipment_as_created->type_id());
		self::assertEquals(self::$location->id(), $equipment_as_created->location_id());
		self::assertEquals($mac_address, $equipment_as_created->mac_address());
		self::assertEquals($timeout, $equipment_as_created->timeout());
		self::assertEquals($is_in_service, $equipment_as_created->is_in_service());
		self::assertEquals($service_minutes, $equipment_as_created->service_minutes());
		self::assertNull($equipment_as_created->ip_address());

		$equipment_as_found = $model->read($equipment_id);

		self::assertNotNull($equipment_as_found);
		self::assertEquals($equipment_id, $equipment_as_found->id());
		self::assertEquals($name, $equipment_as_found->name());
		self::assertEquals(self::$type->id(), $equipment_as_found->type_id());
		self::assertEquals(self::$location->id(), $equipment_as_found->location_id());
		self::assertEquals($mac_address, $equipment_as_found->mac_address());
		self::assertEquals($timeout, $equipment_as_found->timeout());
		self::assertEquals($is_in_service, $equipment_as_found->is_in_service());
		self::assertEquals($service_minutes, $equipment_as_found->service_minutes());
		self::assertNull($equipment_as_found->ip_address());

		$name = '2000W Floodlight';
		$mac_address = 'cdef456789ab';
		$timeout = 120;
		$is_in_service = false;
		$service_minutes = 700;

		$equipment_as_found
			->set_name($name)
			->set_mac_address($mac_address)
			->set_timeout($timeout)
			->set_is_in_service($is_in_service)
			->set_service_minutes($service_minutes);

		$equipment_as_modified = $model->update($equipment_as_found);

		self::assertNotNull($equipment_as_modified);
		self::assertEquals($equipment_id, $equipment_as_modified->id());
		self::assertEquals($name, $equipment_as_modified->name());
		self::assertEquals(self::$type->id(), $equipment_as_modified->type_id());
		self::assertEquals(self::$location->id(), $equipment_as_modified->location_id());
		self::assertEquals($mac_address, $equipment_as_modified->mac_address());
		self::assertEquals($timeout, $equipment_as_modified->timeout());
		self::assertEquals($is_in_service, $equipment_as_modified->is_in_service());
		self::assertEquals($service_minutes, $equipment_as_modified->service_minutes());
		self::assertNull($equipment_as_modified->ip_address());

		$equipment_as_deleted = $model->delete($equipment_id);

		self::assertNotNull($equipment_as_deleted);
		self::assertEquals($equipment_id, $equipment_as_deleted->id());
		self::assertEquals($name, $equipment_as_deleted->name());
		self::assertEquals(self::$type->id(), $equipment_as_deleted->type_id());
		self::assertEquals(self::$location->id(), $equipment_as_deleted->location_id());
		self::assertEquals($mac_address, $equipment_as_deleted->mac_address());
		self::assertEquals($timeout, $equipment_as_deleted->timeout());
		self::assertEquals($is_in_service, $equipment_as_deleted->is_in_service());
		self::assertEquals($service_minutes, $equipment_as_deleted->service_minutes());
		self::assertNull($equipment_as_deleted->ip_address());

		self::assertNull($model->read($equipment_id));
	}

	public function testSearch(): void {
		$model = new EquipmentModel(self::$config);

		$name = '1000W Floodlight';
		$mac_address1 = '0123456789AB';
		$mac_address2 = '0123456789CD';
		$mac_address3 = '0123456789EF';
		$timeout = 0;
		$service_minutes = 500;

		$equipmentId1 = $model->create(
			(new Equipment())
				->set_name($name)
				->set_type(self::$type)
				->set_location(self::$location)
				->set_mac_address($mac_address1)
				->set_timeout($timeout)
				->set_is_in_service(true)
				->set_service_minutes($service_minutes)
		)->id();

		$equipmentId2 = $model->create(
			(new Equipment())
				->set_name($name)
				->set_type(self::$type)
				->set_location(self::$location)
				->set_mac_address($mac_address2)
				->set_timeout($timeout)
				->set_is_in_service(false)
				->set_service_minutes($service_minutes)
		)->id();

		$equipmentId3 = $model->create(
			(new Equipment())
				->set_name($name)
				->set_type(self::$type)
				->set_location(self::$location)
				->set_mac_address($mac_address3)
				->set_timeout($timeout)
				->set_is_in_service(true)
				->set_service_minutes($service_minutes)
		)->id();

		// Test that we can get all equipment
		$equipmentIds = array_map(
			fn (Equipment $equipment) => $equipment->id(),
			$model->search()
		);

		self::assertContains($equipmentId1, $equipmentIds);
		self::assertContains($equipmentId2, $equipmentIds);
		self::assertContains($equipmentId3, $equipmentIds);

		$query = new EquipmentQuery();
		$equipmentIds = array_map(
			fn (Equipment $equipment) => $equipment->id(),
			$model->search($query)
		);

		self::assertContains($equipmentId1, $equipmentIds);
		self::assertContains($equipmentId2, $equipmentIds);
		self::assertContains($equipmentId3, $equipmentIds);

		// Test that we can get in service equipment
		$query = (new EquipmentQuery())->set_exclude_out_of_service(true);
		$equipmentIds = array_map(
			fn (Equipment $equipment) => $equipment->id(),
			$model->search($query)
		);

		self::assertContains($equipmentId1, $equipmentIds);
		self::assertNotContains($equipmentId2, $equipmentIds);
		self::assertContains($equipmentId3, $equipmentIds);

		// Test that we can get equipment by MAC address
		$query = (new EquipmentQuery())->set_mac_address($mac_address3);
		$equipmentIds = array_map(
			fn (Equipment $equipment) => $equipment->id(),
			$model->search($query)
		);

		self::assertNotContains($equipmentId1, $equipmentIds);
		self::assertNotContains($equipmentId2, $equipmentIds);
		self::assertContains($equipmentId3, $equipmentIds);

		// Test that we can get equipment by location
		$query = (new EquipmentQuery())->set_location(self::$location->name());
		$equipmentIds = array_map(
			fn (Equipment $equipment) => $equipment->id(),
			$model->search($query)
		);

		self::assertContains($equipmentId1, $equipmentIds);
		self::assertContains($equipmentId2, $equipmentIds);
		self::assertContains($equipmentId3, $equipmentIds);

		// cleanup
		$model->delete($equipmentId1);
		$model->delete($equipmentId2);
		$model->delete($equipmentId3);
	}
}
