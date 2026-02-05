<?php

declare(strict_types=1);

namespace Test\Portalbox\Model;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Portalbox\Config;
use Portalbox\Enumeration\ChargePolicy;
use Portalbox\Model\ActivationModel;
use Portalbox\Model\EquipmentModel;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Model\LocationModel;
use Portalbox\Type\Equipment;
use Portalbox\Type\EquipmentType;
use Portalbox\Type\Location;

final class ActivationModelTest extends TestCase {
	/** A location that exists in the db */
	private static Location $location;

	/** An equipment type which exists in the db */
	private static EquipmentType $type;

	/** The configuration */
	private static Config $config;

	/** An Equipment/device/portalbox that exists in the db */
	private static Equipment $equipment;

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

		// provision an equipment in the db
		$model = new EquipmentModel(self::$config);

		$name = '1000W Floodlight';
		$mac_address = '0123456789AB';
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

		self::$equipment = $model->create($equipment);
	}

	public static function tearDownAfterClass(): void {
		// deprovision an equipment in the db
		$model = new EquipmentModel(self::$config);
		$model->delete(self::$equipment->id());

		// deprovision a location in the db
		$model = new LocationModel(self::$config);
		$model->delete(self::$location->id());

		// deprovision an equipment type in the db
		$model = new EquipmentTypeModel(self::$config);
		$model->delete(self::$type->id());

		parent::tearDownAfterClass();
	}

	public function testCreateReadDelete(): void {
		$model = new ActivationModel(self::$config);

		self::assertTrue($model->create(self::$equipment->id()));

		$start_time = $model->read(self::$equipment->id());
		self::assertInstanceOf(DateTimeImmutable::class, $start_time);

		self::assertEquals($start_time, $model->delete(self::$equipment->id()));

		self::assertNull($model->read(self::$equipment->id()));
	}
}
