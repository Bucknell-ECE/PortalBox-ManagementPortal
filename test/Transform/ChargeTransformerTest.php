<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Portalbox\Config;

use Portalbox\Entity\Charge;
use Portalbox\Entity\ChargePolicy;
use Portalbox\Entity\Equipment;
use Portalbox\Entity\EquipmentType;
use Portalbox\Entity\Location;
use Portalbox\Entity\Role;
use Portalbox\Entity\User;

use Portalbox\Model\EquipmentModel;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Model\LocationModel;
use Portalbox\Model\UserModel;

use Portalbox\Transform\ChargeTransformer;

final class ChargeTransformerTest extends TestCase {
	/**
	 * A user guananteed to exist in the DB
	 * @var User
	 */
	private static $user;

	/**
	 * A location that exists in the db
	 */
	private static $location;

	/**
	 * An equipment type which exists in the db
	 */
	private static $type;

	/**
	 * An equipment which exists in the db
	 */
	private static $equipment;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		$config = Config::config();

		// provision a user in the db
		$model = new UserModel($config);

		$role_id = 3;	// default id of system defined admin role

		$role = (new Role())
			->set_id($role_id)
			->set_name('administrator')
			->set_description('Administrators')
			->set_is_system_role(false);

		$name = 'Tom Egan';
		$email = 'tom@ficticious.tld';
		$comment = 'Test Monkey';
		$active = TRUE;

		$user = (new User())
			->set_name($name)
			->set_email($email)
			->set_comment($comment)
			->set_is_active($active)
			->set_role($role);

		self::$user = $model->create($user);

		// provision a location in the db
		$model = new LocationModel($config);

		$name = 'Robotics Shop';

		$location = (new Location())
			->set_name($name);

		self::$location = $model->create($location);

		// provision an equipment type in the db
		$model = new EquipmentTypeModel($config);

		$name = 'Floodlight';
		$requires_training = FALSE;
		$charge_policy_id = ChargePolicy::PER_USE;

		$type = (new EquipmentType())
			->set_name($name)
			->set_requires_training($requires_training)
			->set_charge_policy_id($charge_policy_id)
			->set_charge_rate('2.00');

		self::$type = $model->create($type);

		// provision an equipment in the db
		$model = new EquipmentModel($config);

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

		self::$equipment = $model->create($equipment);
	}

	public static function tearDownAfterClass() : void {
		$config = Config::config();

		// deprovision user from the db
		$model = new UserModel($config);
		$model->delete(self::$user->id());

		// deprovision an equipment in the db
		$model = new EquipmentModel($config);
		$model->delete(self::$equipment->id());

		// deprovision a location in the db
		$model = new LocationModel($config);
		$model->delete(self::$location->id());

		// deprovision an equipment type in the db
		$model = new EquipmentTypeModel($config);
		$model->delete(self::$type->id());
	}

	public function testDeserialize(): void {
		$transformer = new ChargeTransformer();

		$id = 42;
		$user_id = self::$user->id();
		$equipment_id = self::$equipment->id();
		$amount = '2.00';
		$charge_policy_id = ChargePolicy::PER_USE;
		$charge_rate = '2.00';
		$charged_time = 25;
		$time = '2020-05-31 10:46:34';

		$data = [
			'id' => $id,
			'user_id' => $user_id,
			'equipment_id' => $equipment_id,
			'amount' => $amount,
			'charge_policy_id' => $charge_policy_id,
			'charge_rate' => $charge_rate,
			'charged_time' => $charged_time,
			'time' => $time
		];

		$charge = $transformer->deserialize($data);

		self::assertNotNull($charge);
		self::assertNull($charge->id());
		self::assertEquals($user_id, $charge->user_id());
		self::assertEquals($equipment_id, $charge->equipment_id());
		self::assertEquals($amount, $charge->amount());
		self::assertEquals($charge_policy_id, $charge->charge_policy_id());
		self::assertEquals($charge_rate, $charge->charge_rate());
		self::assertEquals($charged_time, $charge->charged_time());
		self::assertEquals($time, $charge->time());
	}

	public function testDeserializeInvalidDataUserIDMissing(): void {
		$transformer = new ChargeTransformer();

		$id = 42;
		$equipment_id = self::$equipment->id();
		$amount = '2.00';
		$charge_policy_id = ChargePolicy::PER_USE;
		$charge_rate = '2.00';
		$charged_time = 25;
		$time = '2020-05-31 10:46:34';

		$data = [
			'id' => $id,
			'equipment_id' => $equipment_id,
			'amount' => $amount,
			'charge_policy_id' => $charge_policy_id,
			'charge_rate' => $charge_rate,
			'charged_time' => $charged_time,
			'time' => $time
		];

		$this->expectException(InvalidArgumentException::class);
		$charge = $transformer->deserialize($data);
	}

	public function testDeserializeInvalidDataUserID(): void {
		$transformer = new ChargeTransformer();

		$id = 42;
		$user_id = self::$user->id() + 100;
		$equipment_id = self::$equipment->id();
		$amount = '2.00';
		$charge_policy_id = ChargePolicy::PER_USE;
		$charge_rate = '2.00';
		$charged_time = 25;
		$time = '2020-05-31 10:46:34';

		$data = [
			'id' => $id,
			'user_id' => $user_id,
			'equipment_id' => $equipment_id,
			'amount' => $amount,
			'charge_policy_id' => $charge_policy_id,
			'charge_rate' => $charge_rate,
			'charged_time' => $charged_time,
			'time' => $time
		];

		$this->expectException(InvalidArgumentException::class);
		$charge = $transformer->deserialize($data);
	}

	public function testDeserializeInvalidDataEquipmentIDMissing(): void {
		$transformer = new ChargeTransformer();

		$id = 42;
		$user_id = self::$user->id();
		$amount = '2.00';
		$charge_policy_id = ChargePolicy::PER_USE;
		$charge_rate = '2.00';
		$charged_time = 25;
		$time = '2020-05-31 10:46:34';

		$data = [
			'id' => $id,
			'user_id' => $user_id,
			'amount' => $amount,
			'charge_policy_id' => $charge_policy_id,
			'charge_rate' => $charge_rate,
			'charged_time' => $charged_time,
			'time' => $time
		];

		$this->expectException(InvalidArgumentException::class);
		$charge = $transformer->deserialize($data);
	}

	public function testDeserializeInvalidDataEquipmentID(): void {
		$transformer = new ChargeTransformer();

		$id = 42;
		$user_id = self::$user->id();
		$equipment_id = self::$equipment->id() + 100;
		$amount = '2.00';
		$charge_policy_id = ChargePolicy::PER_USE;
		$charge_rate = '2.00';
		$charged_time = 25;
		$time = '2020-05-31 10:46:34';

		$data = [
			'id' => $id,
			'user_id' => $user_id,
			'equipment_id' => $equipment_id,
			'amount' => $amount,
			'charge_policy_id' => $charge_policy_id,
			'charge_rate' => $charge_rate,
			'charged_time' => $charged_time,
			'time' => $time
		];

		$this->expectException(InvalidArgumentException::class);
		$charge = $transformer->deserialize($data);
	}

	public function testDeserializeInvalidDataAmount(): void {
		$transformer = new ChargeTransformer();

		$id = 42;
		$user_id = self::$user->id();
		$equipment_id = self::$equipment->id();
		$charge_policy_id = ChargePolicy::PER_USE;
		$charge_rate = '2.00';
		$charged_time = 25;
		$time = '2020-05-31 10:46:34';

		$data = [
			'id' => $id,
			'user_id' => $user_id,
			'equipment_id' => $equipment_id,
			'charge_policy_id' => $charge_policy_id,
			'charge_rate' => $charge_rate,
			'charged_time' => $charged_time,
			'time' => $time
		];

		$this->expectException(InvalidArgumentException::class);
		$charge = $transformer->deserialize($data);
	}

	public function testDeserializeInvalidDataChargePolicy(): void {
		$transformer = new ChargeTransformer();

		$id = 42;
		$user_id = self::$user->id();
		$equipment_id = self::$equipment->id();
		$amount = '2.00';
		$charge_rate = '2.00';
		$charged_time = 25;
		$time = '2020-05-31 10:46:34';

		$data = [
			'id' => $id,
			'user_id' => $user_id,
			'equipment_id' => $equipment_id,
			'amount' => $amount,
			'charge_rate' => $charge_rate,
			'charged_time' => $charged_time,
			'time' => $time
		];

		$this->expectException(InvalidArgumentException::class);
		$charge = $transformer->deserialize($data);
	}

	public function testDeserializeInvalidDataChargeRate(): void {
		$transformer = new ChargeTransformer();

		$id = 42;
		$user_id = self::$user->id();
		$equipment_id = self::$equipment->id();
		$amount = '2.00';
		$charge_policy_id = ChargePolicy::PER_USE;
		$charged_time = 25;
		$time = '2020-05-31 10:46:34';

		$data = [
			'id' => $id,
			'user_id' => $user_id,
			'equipment_id' => $equipment_id,
			'amount' => $amount,
			'charge_policy_id' => $charge_policy_id,
			'charged_time' => $charged_time,
			'time' => $time
		];

		$this->expectException(InvalidArgumentException::class);
		$charge = $transformer->deserialize($data);
	}

	public function testDeserializeInvalidDataChargedTime(): void {
		$transformer = new ChargeTransformer();

		$id = 42;
		$user_id = self::$user->id();
		$equipment_id = self::$equipment->id();
		$amount = '2.00';
		$charge_policy_id = ChargePolicy::PER_USE;
		$charge_rate = '2.00';
		$time = '2020-05-31 10:46:34';

		$data = [
			'id' => $id,
			'user_id' => $user_id,
			'equipment_id' => $equipment_id,
			'amount' => $amount,
			'charge_policy_id' => $charge_policy_id,
			'charge_rate' => $charge_rate,
			'time' => $time
		];

		$this->expectException(InvalidArgumentException::class);
		$charge = $transformer->deserialize($data);
	}

	public function testDeserializeInvalidDataTime(): void {
		$transformer = new ChargeTransformer();

		$id = 42;
		$user_id = self::$user->id();
		$equipment_id = self::$equipment->id();
		$amount = '2.00';
		$charge_policy_id = ChargePolicy::PER_USE;
		$charge_rate = '2.00';
		$charged_time = 25;

		$data = [
			'id' => $id,
			'user_id' => $user_id,
			'equipment_id' => $equipment_id,
			'amount' => $amount,
			'charge_policy_id' => $charge_policy_id,
			'charge_rate' => $charge_rate,
			'charged_time' => $charged_time
		];

		$this->expectException(InvalidArgumentException::class);
		$charge = $transformer->deserialize($data);
	}

	public function testSerialize(): void {
		$transformer = new ChargeTransformer();

		$id = 42;
		$amount = '2.00';
		$charge_policy_id = ChargePolicy::PER_USE;
		$charge_rate = '2.00';
		$charged_time = 25;
		$time = '2020-05-31 10:46:34';

		$charge = (new Charge())
			->set_id($id)
			->set_user(self::$user)
			->set_equipment(self::$equipment)
			->set_amount($amount)
			->set_charge_policy_id($charge_policy_id)
			->set_charge_rate($charge_rate)
			->set_charged_time($charged_time)
			->set_time($time);

		$data = $transformer->serialize($charge, true);

		self::assertNotNull($data);
		self::assertArrayHasKey('id', $data);
		self::assertEquals($id, $data['id']);
		self::assertArrayHasKey('user', $data);
		self::assertIsArray($data['user']);
		self::assertArrayHasKey('id', $data['user']);
		self::assertEquals(self::$user->id(), $data['user']['id']);
		self::assertArrayHasKey('equipment', $data);
		self::assertIsArray($data['equipment']);
		self::assertArrayHasKey('id', $data['equipment']);
		self::assertEquals(self::$equipment->id(), $data['equipment']['id']);
		self::assertArrayHasKey('amount', $data);
		self::assertEquals($amount, $data['amount']);
		self::assertArrayHasKey('charge_policy_id', $data);
		self::assertEquals($charge_policy_id, $data['charge_policy_id']);
		self::assertArrayHasKey('charge_rate', $data);
		self::assertEquals($charge_rate, $data['charge_rate']);
		self::assertArrayHasKey('charged_time', $data);
		self::assertEquals($charged_time, $data['charged_time']);
		self::assertArrayHasKey('time', $data);
		self::assertEquals($time, $data['time']);
	}
}