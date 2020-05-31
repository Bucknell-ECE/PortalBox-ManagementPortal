<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Portalbox\Config;
use Portalbox\Entity\ChargePolicy;
use Portalbox\Entity\Equipment;
use Portalbox\Entity\EquipmentType;
use Portalbox\Entity\Location;
use Portalbox\Model\EquipmentModel;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Model\LocationModel;
use Portalbox\Query\EquipmentQuery;

final class EquipmentModelTest extends TestCase {
	/**
	 * A location that exists in the db
	 */
	private static $location;

	/**
	 * An equipment type which exists in the db
	 */
	private static $type;

	/**
	 * The configuration
	 * @var Config
	 */
	private static $config;

	public static function setUpBeforeClass(): void {
		parent::setUp();
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
		$requires_training = FALSE;
		$charge_policy_id = ChargePolicy::NO_CHARGE;

		$type = (new EquipmentType())
			->set_name($name)
			->set_requires_training($requires_training)
			->set_charge_policy_id($charge_policy_id);

		self::$type = $model->create($type);
	}

	public static function tearDownAfterClass() : void {
		// deprovision a location in the db
		$model = new LocationModel(self::$config);
		$model->delete(self::$location->id());

		// deprovision an equipment type in the db
		$model = new EquipmentTypeModel(self::$config);
		$model->delete(self::$type->id());
	}

	public function testCRUD(): void {
		$model = new EquipmentModel(self::$config);

		$name = '1000W Floodlight';
		$mac_address = '0123456789ab';
		$timeout = 0;
		$is_in_service = TRUE;
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

		$name = '2000W Floodlight';
		$mac_address = 'cdef456789ab';
		$timeout = 120;
		$is_in_service = FALSE;
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

		$equipment_as_not_found = $model->read($equipment_id);

		self::assertNull($equipment_as_not_found);
	}

	public function testSearch(): void {
		$model = new EquipmentModel(self::$config);

		$name = '1000W Floodlight';
		$mac_address = '0123456789AB';
		$timeout = 0;
		$is_in_service = TRUE;
		$service_minutes = 500;

		$equipment = (new Equipment())
			->set_name($name)
			->set_type(self::$type)
			->set_location(self::$location)
			->set_mac_address($mac_address)
			->set_timeout($timeout)
			->set_is_in_service($is_in_service)
			->set_service_minutes($service_minutes);

		$equipment_id = ($model->create($equipment))->id();

		$query = new EquipmentQuery();	// get all in_service
		$all_in_service_equipment = $model->search($query);

		self::assertIsArray($all_in_service_equipment);
		self::assertNotEmpty($all_in_service_equipment);
		self::assertContainsOnlyInstancesOf(Equipment::class, $all_in_service_equipment);

		$query = (new EquipmentQuery())->set_location(self::$location->name());	// get all located in location
		$all_equipment_in_location = $model->search($query);

		self::assertIsArray($all_equipment_in_location);
		self::assertNotEmpty($all_equipment_in_location);
		self::assertContainsOnlyInstancesOf(Equipment::class, $all_equipment_in_location);

		// $query = (new EquipmentQuery())->set_location('Deep Space');	// get all located in space
		// $all_equipment_in_space = $model->search($query);

		// self::assertIsArray($all_equipment_in_space);
		// self::assertEmpty($all_equipment_in_space);
		// self::assertContainsOnlyInstancesOf(Equipment::class, $all_equipment_in_space);

		$equipment_as_deleted = $model->delete($equipment_id);
	}
}