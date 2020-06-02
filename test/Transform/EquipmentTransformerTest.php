<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use PortalBox\Config;

use PortalBox\Entity\ChargePolicy;
use PortalBox\Entity\Equipment;
use PortalBox\Entity\EquipmentType;
use PortalBox\Entity\Location;

use PortalBox\Model\EquipmentTypeModel;
use PortalBox\Model\LocationModel;

use Portalbox\Transform\EquipmentTransformer;

final class EquipmentTransformerTest extends TestCase {
	/**
	 * An equipment type guananteed to exist in the DB
	 * @var EquipmentType
	 */
	private static $type;

	/**
	 * A location guananteed to exist in the DB
	 * @var Location
	 */
	private static $location;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		$config = Config::config();

		// provision a location in the db
		$model = new LocationModel($config);

		$name = 'Sculpture Studio';

		$location = (new Location())
			->set_name($name);

		self::$location = $model->create($location);

		// provision a location in the db
		$model = new EquipmentTypeModel($config);

		$name = 'ceramics printer';
		$requires_training = TRUE;
		$charge_rate = "0.01";
		$charge_policy_id = ChargePolicy::PER_MINUTE;

		$type = (new EquipmentType())
			->set_name($name)
			->set_requires_training($requires_training)
			->set_charge_rate($charge_rate)
			->set_charge_policy_id($charge_policy_id);

		self::$type = $model->create($type);
	}

	public static function tearDownAfterClass() : void {
		$config = Config::config();

		// deprovision equipment type from the db
		$model = new EquipmentTypeModel($config);
		$model->delete(self::$type->id());

		// deprovision location from the db
		$model = new LocationModel($config);
		$model->delete(self::$location->id());
	}

	public function testDeserialize(): void {
		$transformer = new EquipmentTransformer();

		$id = 42;
		$name = 'ClayPrint30';
		$type_id = self::$type->id();
		$location_id = self::$location->id();
		$mac_address = '098765abcdef';
		$timeout = 240;
		$in_service = true;
		$in_use = false;

		$data = [
			'id' => $id,
			'name' => $name,
			'type_id' => $type_id,
			'location_id' => $location_id,
			'mac_address' => $mac_address,
			'timeout' => $timeout,
			'in_service' => $in_service,
			'in_use' => $in_use
		];

		$equipment = $transformer->deserialize($data);

		self::assertNotNull($equipment);
		self::assertNull($equipment->id());
		self::assertEquals($type_id, $equipment->type_id());
		self::assertEquals($location_id, $equipment->location_id());
		self::assertEquals($mac_address, $equipment->mac_address());
		self::assertEquals($timeout, $equipment->timeout());
		self::assertEquals($in_service, $equipment->is_in_service());
	}

	public function testDeserializeInvalidDataName(): void {
		$transformer = new EquipmentTransformer();

		$id = 42;
		$type_id = self::$type->id();
		$location_id = self::$location->id();
		$mac_address = '098765abcdef';
		$timeout = 240;
		$in_service = true;
		$in_use = false;

		$data = [
			'id' => $id,
			'type_id' => $type_id,
			'location_id' => $location_id,
			'mac_address' => $mac_address,
			'timeout' => $timeout,
			'in_service' => $in_service,
			'in_use' => $in_use
		];

		$this->expectException(InvalidArgumentException::class);
		$equipment = $transformer->deserialize($data);
	}

	public function testDeserializeInvalidDataMissingType(): void {
		$transformer = new EquipmentTransformer();

		$id = 42;
		$name = 'ClayPrint30';
		$location_id = self::$location->id();
		$mac_address = '098765abcdef';
		$timeout = 240;
		$in_service = true;
		$in_use = false;

		$data = [
			'id' => $id,
			'name' => $name,
			'location_id' => $location_id,
			'mac_address' => $mac_address,
			'timeout' => $timeout,
			'in_service' => $in_service,
			'in_use' => $in_use
		];

		$this->expectException(InvalidArgumentException::class);
		$equipment = $transformer->deserialize($data);
	}

	public function testDeserializeInvalidDataType(): void {
		$transformer = new EquipmentTransformer();

		$id = 42;
		$name = 'ClayPrint30';
		$type_id = self::$type->id() + 137;
		$location_id = self::$location->id();
		$mac_address = '098765abcdef';
		$timeout = 240;
		$in_service = true;
		$in_use = false;

		$data = [
			'id' => $id,
			'name' => $name,
			'type_id' => $type_id,
			'location_id' => $location_id,
			'mac_address' => $mac_address,
			'timeout' => $timeout,
			'in_service' => $in_service,
			'in_use' => $in_use
		];

		$this->expectException(InvalidArgumentException::class);
		$equipment = $transformer->deserialize($data);
	}

	public function testDeserializeInvalidDataMissingLocation(): void {
		$transformer = new EquipmentTransformer();

		$id = 42;
		$name = 'ClayPrint30';
		$type_id = self::$type->id();
		$mac_address = '098765abcdef';
		$timeout = 240;
		$in_service = true;
		$in_use = false;

		$data = [
			'id' => $id,
			'name' => $name,
			'type_id' => $type_id,
			'mac_address' => $mac_address,
			'timeout' => $timeout,
			'in_service' => $in_service,
			'in_use' => $in_use
		];

		$this->expectException(InvalidArgumentException::class);
		$equipment = $transformer->deserialize($data);
	}

	public function testDeserializeInvalidDataLocation(): void {
		$transformer = new EquipmentTransformer();

		$id = 42;
		$name = 'ClayPrint30';
		$type_id = self::$type->id();
		$location_id = self::$location->id() + 137;
		$mac_address = '098765abcdef';
		$timeout = 240;
		$in_service = true;
		$in_use = false;

		$data = [
			'id' => $id,
			'name' => $name,
			'type_id' => $type_id,
			'location_id' => $location_id,
			'mac_address' => $mac_address,
			'timeout' => $timeout,
			'in_service' => $in_service,
			'in_use' => $in_use
		];

		$this->expectException(InvalidArgumentException::class);
		$equipment = $transformer->deserialize($data);
	}

	public function testDeserializeInvalidDataMACAddress(): void {
		$transformer = new EquipmentTransformer();

		$id = 42;
		$name = 'ClayPrint30';
		$type_id = self::$type->id();
		$location_id = self::$location->id();
		$timeout = 240;
		$in_service = true;
		$in_use = false;

		$data = [
			'id' => $id,
			'name' => $name,
			'type_id' => $type_id,
			'location_id' => $location_id,
			'timeout' => $timeout,
			'in_service' => $in_service,
			'in_use' => $in_use
		];

		$this->expectException(InvalidArgumentException::class);
		$equipment = $transformer->deserialize($data);
	}

	public function testDeserializeInvalidDataTimeOut(): void {
		$transformer = new EquipmentTransformer();

		$id = 42;
		$name = 'ClayPrint30';
		$type_id = self::$type->id();
		$location_id = self::$location->id();
		$mac_address = '098765abcdef';
		$in_service = true;
		$in_use = false;

		$data = [
			'id' => $id,
			'name' => $name,
			'type_id' => $type_id,
			'location_id' => $location_id,
			'mac_address' => $mac_address,
			'in_service' => $in_service,
			'in_use' => $in_use
		];

		$this->expectException(InvalidArgumentException::class);
		$equipment = $transformer->deserialize($data);
	}

	public function testDeserializeInvalidDataInService(): void {
		$transformer = new EquipmentTransformer();

		$id = 42;
		$name = 'ClayPrint30';
		$type_id = self::$type->id();
		$location_id = self::$location->id();
		$mac_address = '098765abcdef';
		$timeout = 240;
		$in_use = false;

		$data = [
			'id' => $id,
			'name' => $name,
			'type_id' => $type_id,
			'location_id' => $location_id,
			'mac_address' => $mac_address,
			'timeout' => $timeout,
			'in_use' => $in_use
		];

		$this->expectException(InvalidArgumentException::class);
		$equipment = $transformer->deserialize($data);
	}

	public function testSerialize(): void {
		$transformer = new EquipmentTransformer();

		$id = 42;
		$name = 'ClayPrint30';
		$mac_address = 'abcdef098765';
		$timeout = 240;
		$in_service = true;
		$in_use = false;

		$equipment = (new Equipment())
			->set_id($id)
			->set_name($name)
			->set_type(self::$type)
			->set_location(self::$location)
			->set_mac_address($mac_address)
			->set_timeout($timeout)
			->set_is_in_service($in_service)
			->set_is_in_use($in_use);

		$data = $transformer->serialize($equipment, true);

		self::assertNotNull($data);
		self::assertArrayHasKey('id', $data);
		self::assertEquals($id, $data['id']);
		self::assertArrayHasKey('name', $data);
		self::assertEquals($name, $data['name']);
		self::assertArrayHasKey('type_id', $data);
		self::assertEquals(self::$type->id(), $data['type_id']);
		self::assertArrayHasKey('type', $data);
		self::assertEquals(self::$type->name(), $data['type']);
		self::assertArrayHasKey('location_id', $data);
		self::assertEquals(self::$location->id(), $data['location_id']);
		self::assertArrayHasKey('location', $data);
		self::assertEquals(self::$location->name(), $data['location']);
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