<?php

declare(strict_types=1);

namespace Test\Portalbox\Model;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Portalbox\Config;
use Portalbox\Enumeration\ChargePolicy;
use Portalbox\Model\ChargeModel;
use Portalbox\Model\EquipmentModel;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Model\LocationModel;
use Portalbox\Model\UserModel;
use Portalbox\Query\ChargeQuery;
use Portalbox\Type\Charge;
use Portalbox\Type\Equipment;
use Portalbox\Type\EquipmentType;
use Portalbox\Type\Location;
use Portalbox\Type\Role;
use Portalbox\Type\User;

final class ChargeModelTest extends TestCase {
	/**
	 * A user guaranteed to exist in the DB
	 */
	private static User $user;

	/**
	 * A location that exists in the db
	 */
	private static Location $location;

	/**
	 * An equipment type which exists in the db
	 */
	private static EquipmentType $type;

	/**
	 * An equipment which exists in the db
	 */
	private static Equipment $equipment;

	/**
	 * The configuration
	 */
	private static Config $config;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		self::$config = Config::config();

		// provision a user in the db
		$model = new UserModel(self::$config);

		$role_id = 3;	// default id of system defined admin role

		$role = (new Role())
			->set_id($role_id);

		$name = 'Tom Egan';
		$email = 'tom@ficticious.tld';
		$comment = 'Test Monkey';
		$active = true;

		$user = (new User())
			->set_name($name)
			->set_email($email)
			->set_comment($comment)
			->set_is_active($active)
			->set_role($role);

		self::$user = $model->create($user);

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
		// deprovision a location in the db
		$model = new UserModel(self::$config);
		$model->delete(self::$user->id());

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

	public function testCreateReadUpdateDelete(): void {
		$model = new ChargeModel(self::$config);

		$equipment_id = self::$equipment->id();
		$user_id = self::$user->id();
		$amount = '2.00';
		$time = '2020-04-22 21:44:55';
		$charge_policy = ChargePolicy::PER_USE;
		$charge_rate = '0.05';
		$charged_time = 40;

		$charge = $model->create(
			(new Charge())
				->set_equipment_id($equipment_id)
				->set_user_id($user_id)
				->set_amount($amount)
				->set_time($time)
				->set_charge_policy($charge_policy)
				->set_charge_rate($charge_rate)
				->set_charged_time($charged_time)
		);

		self::assertInstanceOf(Charge::class, $charge);
		$id = $charge->id();
		self::assertIsInt($id);
		self::assertSame($equipment_id, $charge->equipment_id());
		self::assertSame($user_id, $charge->user_id());
		self::assertSame($amount, $charge->amount());
		self::assertSame($time, $charge->time());
		self::assertSame($charge_policy, $charge->charge_policy());
		self::assertSame($charge_rate, $charge->charge_rate());
		self::assertSame($charged_time, $charge->charged_time());

		$charge = $model->read($id);

		self::assertInstanceOf(Charge::class, $charge);
		self::assertSame($id, $charge->id());
		self::assertSame($equipment_id, $charge->equipment_id());
		self::assertSame($user_id, $charge->user_id());
		self::assertSame($amount, $charge->amount());
		self::assertSame($time, $charge->time());
		self::assertSame($charge_policy, $charge->charge_policy());
		self::assertSame($charge_rate, $charge->charge_rate());
		self::assertSame($charged_time, $charge->charged_time());

		self::assertInstanceOf(Equipment::class, $charge->equipment());
		self::assertSame($charge->equipment()->id(), $equipment_id);
		self::assertSame(self::$equipment->name(), $charge->equipment_name());

		self::assertInstanceOf(User::class, $charge->user());
		self::assertSame($charge->user()->id(), $user_id);
		self::assertSame(self::$user->name(), $charge->user_name());

		$amount = '3.00';
		$time = '2020-04-21 08:09:10';
		$charge_policy = ChargePolicy::MANUALLY_ADJUSTED;
		$charge_rate = '0.10';
		$charged_time = 30;

		$charge = $model->update(
			(new Charge())
				->set_id($id)
				->set_equipment_id($equipment_id)
				->set_user_id($user_id)
				->set_amount($amount)
				->set_time($time)
				->set_charge_policy($charge_policy)
				->set_charge_rate($charge_rate)
				->set_charged_time($charged_time)
		);

		self::assertInstanceOf(Charge::class, $charge);
		self::assertEquals($id, $charge->id());
		self::assertEquals($equipment_id, $charge->equipment_id());
		self::assertEquals($user_id, $charge->user_id());
		self::assertEquals($amount, $charge->amount());
		self::assertEquals($time, $charge->time());
		self::assertEquals($charge_policy, $charge->charge_policy());
		self::assertEquals($charge_rate, $charge->charge_rate());
		self::assertEquals($charged_time, $charge->charged_time());

		$charge = $model->read($id);

		self::assertInstanceOf(Charge::class, $charge);
		self::assertSame($id, $charge->id());
		self::assertSame($equipment_id, $charge->equipment_id());
		self::assertSame($user_id, $charge->user_id());
		self::assertSame($amount, $charge->amount());
		self::assertSame($time, $charge->time());
		self::assertSame($charge_policy, $charge->charge_policy());
		self::assertSame($charge_rate, $charge->charge_rate());
		self::assertSame($charged_time, $charge->charged_time());

		self::assertInstanceOf(Equipment::class, $charge->equipment());
		self::assertSame($charge->equipment()->id(), $equipment_id);
		self::assertSame(self::$equipment->name(), $charge->equipment_name());

		self::assertInstanceOf(User::class, $charge->user());
		self::assertSame($charge->user()->id(), $user_id);
		self::assertSame(self::$user->name(), $charge->user_name());

		$charge = $model->delete($id);

		self::assertNotNull($charge);
		self::assertEquals($id, $charge->id());
		self::assertEquals($equipment_id, $charge->equipment_id());
		self::assertEquals($user_id, $charge->user_id());
		self::assertEquals($amount, $charge->amount());
		self::assertEquals($time, $charge->time());
		self::assertEquals($charge_policy, $charge->charge_policy());
		self::assertEquals($charge_rate, $charge->charge_rate());
		self::assertEquals($charged_time, $charge->charged_time());

		self::assertNull($model->read($id));
	}

	public function testSearch(): void {
		$model = new ChargeModel(self::$config);

		$equipment_id = self::$equipment->id();
		$user_id = self::$user->id();
		$amount = '2.00';
		$time1 = '2020-04-22 21:44:55';
		$time2 = '2021-04-22 21:44:55';
		$time3 = '2022-04-22 21:44:55';
		$charge_policy = ChargePolicy::PER_USE;
		$charge_rate = '0.05';
		$charged_time = 40;

		$charge1Id = $model->create(
			(new Charge())
				->set_equipment_id($equipment_id)
				->set_user_id($user_id)
				->set_amount($amount)
				->set_time($time1)
				->set_charge_policy($charge_policy)
				->set_charge_rate($charge_rate)
				->set_charged_time($charged_time)
		)->id();

		$charge2Id = $model->create(
			(new Charge())
				->set_equipment_id($equipment_id)
				->set_user_id($user_id)
				->set_amount($amount)
				->set_time($time2)
				->set_charge_policy($charge_policy)
				->set_charge_rate($charge_rate)
				->set_charged_time($charged_time)
		)->id();

		$charge3Id = $model->create(
			(new Charge())
				->set_equipment_id($equipment_id)
				->set_user_id($user_id)
				->set_amount($amount)
				->set_time($time3)
				->set_charge_policy($charge_policy)
				->set_charge_rate($charge_rate)
				->set_charged_time($charged_time)
		)->id();

		$query = new ChargeQuery();	// get all charges
		$chargeIds = array_map(
			fn (Charge $charge) => $charge->id(),
			$model->search($query)
		);

		self::assertContains($charge1Id, $chargeIds);
		self::assertContains($charge2Id, $chargeIds);
		self::assertContains($charge3Id, $chargeIds);

		// Check that we can query charges by user id
		$query = (new ChargeQuery())->set_user_id($user_id);
		$chargeIds = array_map(
			fn (Charge $charge) => $charge->id(),
			$model->search($query)
		);

		self::assertContains($charge1Id, $chargeIds);
		self::assertContains($charge2Id, $chargeIds);
		self::assertContains($charge3Id, $chargeIds);

		// check that we can query charges by equipment id
		$query = (new ChargeQuery())->set_equipment_id($equipment_id);
		$chargeIds = array_map(
			fn (Charge $charge) => $charge->id(),
			$model->search($query)
		);

		self::assertContains($charge1Id, $chargeIds);
		self::assertContains($charge2Id, $chargeIds);
		self::assertContains($charge3Id, $chargeIds);

		// check that we can query charges before a timestamp
		$query = (new ChargeQuery())
			->set_on_or_before(new DateTimeImmutable($time2));
		$chargeIds = array_map(
			fn (Charge $charge) => $charge->id(),
			$model->search($query)
		);

		self::assertContains($charge1Id, $chargeIds);
		self::assertContains($charge2Id, $chargeIds);
		self::assertNotContains($charge3Id, $chargeIds);

		// check that we can query charges after a timestamp
		$query = (new ChargeQuery())
			->set_on_or_after(new DateTimeImmutable($time2));
		$chargeIds = array_map(
			fn (Charge $charge) => $charge->id(),
			$model->search($query)
		);

		self::assertNotContains($charge1Id, $chargeIds);
		self::assertContains($charge2Id, $chargeIds);
		self::assertContains($charge3Id, $chargeIds);

		// cleanup
		$model->delete($charge1Id);
		$model->delete($charge2Id);
		$model->delete($charge3Id);
	}
}
