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

final class ChargeModelTest extends TestCase {
	/**
	 * A user guananteed to exist in the DB
	 * @var User
	 */
	private $user;

	/**
	 * A location that exists in the db
	 */
	private $location;

	/**
	 * An equipment type which exists in the db
	 */
	private $type;

	/**
	 * An equipment which exists in the db
	 */
	private $equipment;

	/**
	 * The configuration
	 * @var Config
	 */
	private $config;

	public function setUp(): void {
		parent::setUp();
		$this->config = Config::config();

		// provision a user in the db
		$model = new UserModel($this->config);

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

		$this->user = $model->create($user);

		// provision a location in the db
		$model = new LocationModel($this->config);

		$name = 'Robotics Shop';

		$location = (new Location())
			->set_name($name);

		$this->location = $model->create($location);

		// provision an equipment type in the db
		$model = new EquipmentTypeModel($this->config);

		$name = 'Floodlight';
		$requires_training = FALSE;
		$charge_policy_id = ChargePolicy::NO_CHARGE;

		$type = (new EquipmentType())
			->set_name($name)
			->set_requires_training($requires_training)
			->set_charge_policy_id($charge_policy_id);

		$this->type = $model->create($type);

		// provision an equipment in the db
		$model = new EquipmentModel($this->config);

		$name = '1000W Floodlight';
		$mac_address = '0123456789AB';
		$timeout = 0;
		$is_in_service = TRUE;
		$service_minutes = 500;

		$equipment = (new Equipment())
			->set_name($name)
			->set_type($this->type)
			->set_location($this->location)
			->set_mac_address($mac_address)
			->set_timeout($timeout)
			->set_is_in_service($is_in_service)
			->set_service_minutes($service_minutes);

		$this->equipment = $model->create($equipment);
	}

	public function tearDown() : void {
		// deprovision a location in the db
		$model = new UserModel($this->config);
		$model->delete($this->user->id());

		// deprovision an equipment in the db
		$model = new EquipmentModel($this->config);
		$model->delete($this->equipment->id());

		// deprovision a location in the db
		$model = new LocationModel($this->config);
		$model->delete($this->location->id());

		// deprovision an equipment type in the db
		$model = new EquipmentTypeModel($this->config);
		$model->delete($this->type->id());
	}

	public function testModel(): void {
		$model = new ChargeModel($this->config);

		$equipment_id = $this->equipment->id();
		$user_id = $this->user->id();
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
}