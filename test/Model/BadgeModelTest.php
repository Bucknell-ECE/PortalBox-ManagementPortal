<?php

declare(strict_types=1);

namespace Test\Portalbox\Model;

use PHPUnit\Framework\TestCase;
use Portalbox\Config;
use Portalbox\Enumeration\ChargePolicy;
use Portalbox\Model\BadgeModel;
use Portalbox\Model\CardModel;
use Portalbox\Model\EquipmentModel;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Model\LocationModel;
use Portalbox\Model\LoggedEventModel;
use Portalbox\Model\UserModel;
use Portalbox\Type\Equipment;
use Portalbox\Type\EquipmentType;
use Portalbox\Type\Location;
use Portalbox\Type\LoggedEvent;
use Portalbox\Type\LoggedEventType;
use Portalbox\Type\Role;
use Portalbox\Type\User;
use Portalbox\Type\UserCard;

/**
 * Test BadgeModel... because LoggedEvents are read only we need to
 * be creative to test it. Therefore we will create Logged event using
 * the underlying connection then the model to read them before using
 * the underlying connection again to delete them.
 */
final class BadgeModelTest extends TestCase {
	private static Config $config;
	private static EquipmentType $type1;
	private static EquipmentType $type2;
	private static Equipment $equipment1;
	private static Equipment $equipment2;
	private static Location $location;
	private static User $user;
	private static UserCard $card;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		self::$config = Config::config();

		// provision a location in the db
		$model = new LocationModel(self::$config);
		self::$location = $model->create(
			(new Location())
				->set_name('Robotics Shop')
		);

		// provision equipment types in the db
		$model = new EquipmentTypeModel(self::$config);
		self::$type1 = $model->create(
			(new EquipmentType())
				->set_name('Laser Cutter')
				->set_requires_training(false)
				->set_charge_policy(ChargePolicy::NO_CHARGE)
				->set_allow_proxy(false)
		);
		self::$type2 = $model->create(
			(new EquipmentType())
				->set_name('3D Printer')
				->set_requires_training(false)
				->set_charge_policy(ChargePolicy::NO_CHARGE)
				->set_allow_proxy(false)
		);

		// provision equipment in the db
		$model = new EquipmentModel(self::$config);
		self::$equipment1 = $model->create(
			(new Equipment())
				->set_name('Laser Cutter 1')
				->set_type(self::$type1)
				->set_location(self::$location)
				->set_mac_address('0123456789ab')
				->set_timeout(0)
				->set_is_in_service(true)
				->set_service_minutes(500)
		);
		self::$equipment2 = $model->create(
			(new Equipment())
				->set_name('Printmatic 2000')
				->set_type(self::$type2)
				->set_location(self::$location)
				->set_mac_address('0123456789ac')
				->set_timeout(0)
				->set_is_in_service(true)
				->set_service_minutes(500)
		);

		// provision a user
		$model = new UserModel(self::$config);
		self::$user = $model->create(
			(new User())
				->set_name('Tom Egan')
				->set_email('tom@ficticious.tld')
				->set_comment('')
				->set_is_active(true)
				->set_role((new Role())->set_id(3))
				->set_authorizations([
					self::$type1->id(),
					self::$type2->id()
				])
		);

		// provision a user card
		$model = new CardModel(self::$config);
		self::$card = $model->create(
			(new UserCard())
				->set_id(622347165)
				->set_user_id(self::$user->id())
		);
	}

	public static function tearDownAfterClass(): void {
		$model = new CardModel(self::$config);
		$model->delete(self::$card->id());

		$model = new UserModel(self::$config);
		$model->delete(self::$user->id());

		$model = new EquipmentModel(self::$config);
		$model->delete(self::$equipment1->id());
		$model->delete(self::$equipment2->id());

		$model = new EquipmentTypeModel(self::$config);
		$model->delete(self::$type1->id());
		$model->delete(self::$type2->id());

		$model = new LocationModel(self::$config);
		$model->delete(self::$location->id());

		parent::tearDownAfterClass();
	}

	public function testCountForUser(): void {
		$model = new LoggedEventModel(self::$config);

		$card_id = self::$card->id();
		$equipment1_id = self::$equipment1->id();
		$equipment2_id = self::$equipment2->id();

		$eventId1 = $model->create(
			(new LoggedEvent())
				->set_type_id(LoggedEventType::STARTUP_COMPLETE)
				->set_equipment_id($equipment1_id)
				->set_time('2010-09-10 13:55:42')
		)->id();

		$eventId2 = $model->create(
			(new LoggedEvent())
				->set_type_id(LoggedEventType::SUCCESSFUL_AUTHENTICATION)
				->set_card_id($card_id)
				->set_equipment_id($equipment1_id)
				->set_time('2010-09-11 08:09:10')
		)->id();

		$eventId3 = $model->create(
			(new LoggedEvent())
				->set_type_id(LoggedEventType::SUCCESSFUL_AUTHENTICATION)
				->set_card_id($card_id)
				->set_equipment_id($equipment2_id)
				->set_time('2010-09-11 09:10:11')
		)->id();

		$eventId4 = $model->create(
			(new LoggedEvent())
				->set_type_id(LoggedEventType::SUCCESSFUL_AUTHENTICATION)
				->set_card_id($card_id)
				->set_equipment_id($equipment1_id)
				->set_time('2010-09-12 10:11:12')
		)->id();

		$badge_model = new BadgeModel(self::$config);
		$counts = $badge_model->countForUser(self::$user->id());
		self::assertIsArray($counts);
		self::assertArrayHasKey(self::$type1->id(), $counts);
		self::assertSame(2, $counts[self::$type1->id()]);
		self::assertArrayHasKey(self::$type2->id(), $counts);
		self::assertSame(1, $counts[self::$type2->id()]);

		// drop to SQL to cleanup
		$statement = $model
			->configuration()
			->writable_db_connection()
			->prepare('DELETE FROM log WHERE id = ?');
		self::assertTrue($statement->execute([$eventId1]));
		self::assertTrue($statement->execute([$eventId2]));
		self::assertTrue($statement->execute([$eventId3]));
		self::assertTrue($statement->execute([$eventId4]));
	}
}
