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
use Portalbox\Model\ChargeModel;
use Portalbox\Model\EquipmentModel;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Model\LocationModel;
use Portalbox\Model\UserModel;
use Portalbox\Query\ChargeQuery;

final class ChargeModelTest extends TestCase {
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

	/**
	 * The configuration
	 * @var Config
	 */
	private static $config;

	public static function setUpBeforeClass(): void {
		parent::setUp();
		self::$config = Config::config();

		// provision a user in the db
		$model = new UserModel(self::$config);

		$role_id = 3;	// default id of system defined admin role

		$role = (new Role())
			->set_id($role_id);

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

		// provision an equipment in the db
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

		self::$equipment = $model->create($equipment);
	}

	public static function tearDownAfterClass() : void {
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
	}

	public function testModel(): void {
		$model = new ChargeModel(self::$config);

		$equipment_id = self::$equipment->id();
		$user_id = self::$user->id();
		$amount = '2.00';
		$time = '2020-04-22 21:44:55';
		$charge_policy_id = ChargePolicy::PER_USE;
		$charge_rate = '0.05';
		$charged_time = 40;

		$charge = (new Charge())
			->set_equipment_id($equipment_id)
			->set_user_id($user_id)
			->set_amount($amount)
			->set_time($time)
			->set_charge_policy_id($charge_policy_id)
			->set_charge_rate($charge_rate)
			->set_charged_time($charged_time);

		$charge_as_created = $model->create($charge);

		$charge_id = $charge_as_created->id();
		self::assertIsInt($charge_id);
		self::assertEquals($equipment_id, $charge_as_created->equipment_id());
		self::assertEquals($user_id, $charge_as_created->user_id());
		self::assertEquals($amount, $charge_as_created->amount());
		self::assertEquals($time, $charge_as_created->time());
		self::assertEquals($charge_policy_id, $charge_as_created->charge_policy_id());
		self::assertEquals($charge_rate, $charge_as_created->charge_rate());
		self::assertEquals($charged_time, $charge_as_created->charged_time());

		$charge_as_found = $model->read($charge_id);

		self::assertNotNull($charge_as_found);
		self::assertEquals($charge_id, $charge_as_found->id());
		self::assertEquals($equipment_id, $charge_as_found->equipment_id());
		self::assertEquals($user_id, $charge_as_found->user_id());
		self::assertEquals($amount, $charge_as_found->amount());
		self::assertEquals($time, $charge_as_found->time());
		self::assertEquals($charge_policy_id, $charge_as_found->charge_policy_id());
		self::assertEquals($charge_rate, $charge_as_found->charge_rate());
		self::assertEquals($charged_time, $charge_as_found->charged_time());

		$amount = '3.00';
		$time = '2020-04-21 08:09:10';
		$charge_policy_id = ChargePolicy::MANUALLY_ADJUSTED;
		$charge_rate = '0.10';
		$charged_time = 30;

		$charge_as_found
			->set_amount($amount)
			->set_time($time)
			->set_charge_policy_id($charge_policy_id)
			->set_charge_rate($charge_rate)
			->set_charged_time($charged_time);

		$charge_as_modified = $model->update($charge_as_found);

		self::assertNotNull($charge_as_modified);
		self::assertEquals($charge_id, $charge_as_modified->id());
		self::assertEquals($equipment_id, $charge_as_modified->equipment_id());
		self::assertEquals($user_id, $charge_as_modified->user_id());
		self::assertEquals($amount, $charge_as_modified->amount());
		self::assertEquals($time, $charge_as_modified->time());
		self::assertEquals($charge_policy_id, $charge_as_modified->charge_policy_id());
		self::assertEquals($charge_rate, $charge_as_modified->charge_rate());
		self::assertEquals($charged_time, $charge_as_modified->charged_time());

		$charge_as_deleted = $model->delete($charge_id);

		self::assertNotNull($charge_as_deleted);
		self::assertEquals($charge_id, $charge_as_deleted->id());
		self::assertEquals($equipment_id, $charge_as_deleted->equipment_id());
		self::assertEquals($user_id, $charge_as_deleted->user_id());
		self::assertEquals($amount, $charge_as_deleted->amount());
		self::assertEquals($time, $charge_as_deleted->time());
		self::assertEquals($charge_policy_id, $charge_as_deleted->charge_policy_id());
		self::assertEquals($charge_rate, $charge_as_deleted->charge_rate());
		self::assertEquals($charged_time, $charge_as_deleted->charged_time());

		$charge_as_not_found = $model->read($charge_id);

		self::assertNull($charge_as_not_found);
	}

	public function testSearch(): void {
		$model = new ChargeModel(self::$config);

		$equipment_id = self::$equipment->id();
		$user_id = self::$user->id();
		$amount = '2.00';
		$time = '2020-04-22 21:44:55';
		$charge_policy_id = ChargePolicy::PER_USE;
		$charge_rate = '0.05';
		$charged_time = 40;

		$charge = (new Charge())
			->set_equipment_id($equipment_id)
			->set_user_id($user_id)
			->set_amount($amount)
			->set_time($time)
			->set_charge_policy_id($charge_policy_id)
			->set_charge_rate($charge_rate)
			->set_charged_time($charged_time);

		$charge_as_created = $model->create($charge);

		$charge_id = $charge_as_created->id();

		$query = new ChargeQuery();	// get all charges
		$all_charges = $model->search($query);

		self::assertIsArray($all_charges);
		self::assertNotEmpty($all_charges);
		self::assertContainsOnlyInstancesOf(Charge::class, $all_charges);

		$query = (new ChargeQuery())->set_user_id($user_id);	// get all charges for user
		$all_charges_for_user = $model->search($query);

		self::assertIsArray($all_charges_for_user);
		self::assertNotEmpty($all_charges_for_user);
		self::assertContainsOnlyInstancesOf(Charge::class, $all_charges_for_user);

		$query = (new ChargeQuery())->set_equipment_id($equipment_id);	// get all charges for equipment
		$all_charges_for_equipment = $model->search($query);

		self::assertIsArray($all_charges_for_equipment);
		self::assertNotEmpty($all_charges_for_equipment);
		self::assertContainsOnlyInstancesOf(Charge::class, $all_charges_for_equipment);

		$query = (new ChargeQuery())->set_on_or_before('2020-04-23 00:00:00');	// get all charges before 2020-04-23
		$all_charges_before_date = $model->search($query);

		self::assertIsArray($all_charges_before_date);
		self::assertNotEmpty($all_charges_before_date);
		self::assertContainsOnlyInstancesOf(Charge::class, $all_charges_before_date);

		$query = (new ChargeQuery())->set_on_or_after('2020-04-22 00:00:00');	// get all charges on or after 2020-04-22
		$all_charges_after_date = $model->search($query);

		self::assertIsArray($all_charges_after_date);
		self::assertNotEmpty($all_charges_after_date);
		self::assertContainsOnlyInstancesOf(Charge::class, $all_charges_after_date);

		$model->delete($charge_id);
	}
}