<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Portalbox\Config;

use Portalbox\Entity\ChargePolicy;
use Portalbox\Entity\Equipment;
use Portalbox\Entity\EquipmentType;
use Portalbox\Entity\Location;
use Portalbox\Entity\LoggedEvent;
use Portalbox\Entity\LoggedEventType;
use Portalbox\Entity\Role;
use Portalbox\Entity\User;

use Portalbox\Model\EquipmentModel;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Model\LocationModel;
use Portalbox\Model\UserModel;

use Portalbox\Transform\LoggedEventTransformer;

final class LoggedEventTransformerTest extends TestCase {
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
		$event_policy_id = ChargePolicy::PER_USE;

		$type = (new EquipmentType())
			->set_name($name)
			->set_requires_training($requires_training)
			->set_charge_policy_id($event_policy_id)
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

	public function testSerialize(): void {
		$transformer = new LoggedEventTransformer();

		$id = 42;
		$time = '2020-05-31 10:46:34';
		$card_id = 1928376451092837465;
		$type_id = LoggedEventType::SUCESSFUL_AUTHENTICATION;

		$event = (new LoggedEvent())
			->set_id($id)
			->set_user(self::$user)
			->set_equipment(self::$equipment)
			->set_type_id($type_id)
			->set_card_id($card_id)
			->set_time($time);

		$data = $transformer->serialize($event, true);

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
		self::assertArrayHasKey('type_id', $data);
		self::assertEquals($type_id, $data['type_id']);
		self::assertArrayHasKey('card_id', $data);
		self::assertEquals($card_id, $data['card_id']);
		self::assertArrayHasKey('time', $data);
		self::assertEquals($time, $data['time']);
	}
}