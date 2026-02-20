<?php

declare(strict_types=1);

namespace Test\Portalbox\Model;

use PHPUnit\Framework\TestCase;
use Portalbox\Config;
use Portalbox\Enumeration\ChargePolicy;
use Portalbox\Enumeration\LoggedEventType;
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
	private static User $user1;
	private static User $user2;
	private static User $user3;
	private static UserCard $card1;
	private static UserCard $card2;
	private static UserCard $card3;
	private static UserCard $card4;

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

		// provision two active and one inactive users
		$model = new UserModel(self::$config);
		self::$user1 = $model->create(
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

		self::$user2 = $model->create(
			(new User())
				->set_name('Toby')
				->set_email('toby@ficticious.tld')
				->set_comment('')
				->set_is_active(false)
				->set_role((new Role())->set_id(2))
				->set_authorizations([
					self::$type1->id(),
					self::$type2->id()
				])
		);

		self::$user3 = $model->create(
			(new User())
				->set_name('Sebastian')
				->set_email('sebastian@ficticious.tld')
				->set_comment('')
				->set_is_active(true)
				->set_role((new Role())->set_id(2))
				->set_authorizations([
					self::$type1->id(),
					self::$type2->id()
				])
		);

		// provision cards for the users
		$model = new CardModel(self::$config);
		self::$card1 = $model->create(
			(new UserCard())
				->set_id(622347165)
				->set_user_id(self::$user1->id())
		);

		$model = new CardModel(self::$config);
		self::$card2 = $model->create(
			(new UserCard())
				->set_id(622347164)
				->set_user_id(self::$user1->id())
		);

		$model = new CardModel(self::$config);
		self::$card3 = $model->create(
			(new UserCard())
				->set_id(622347163)
				->set_user_id(self::$user2->id())
		);

		$model = new CardModel(self::$config);
		self::$card4 = $model->create(
			(new UserCard())
				->set_id(622347162)
				->set_user_id(self::$user3->id())
		);
	}

	public static function tearDownAfterClass(): void {
		$model = new CardModel(self::$config);
		$model->delete(self::$card1->id());
		$model->delete(self::$card2->id());
		$model->delete(self::$card3->id());
		$model->delete(self::$card4->id());

		$model = new UserModel(self::$config);
		$model->delete(self::$user1->id());
		$model->delete(self::$user2->id());
		$model->delete(self::$user3->id());

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

		$card1_id = self::$card1->id();
		$card2_id = self::$card2->id();
		$equipment1_id = self::$equipment1->id();
		$equipment2_id = self::$equipment2->id();

		$eventId1 = $model->create(
			(new LoggedEvent())
				->set_type(LoggedEventType::STARTUP_COMPLETE)
				->set_equipment_id($equipment1_id)
				->set_time('2010-09-10 13:55:42')
		)->id();

		$eventId2 = $model->create(
			(new LoggedEvent())
				->set_type(LoggedEventType::DEAUTHENTICATION)
				->set_card_id($card1_id)
				->set_equipment_id($equipment1_id)
				->set_time('2010-09-11 08:09:10')
		)->id();

		$eventId3 = $model->create(
			(new LoggedEvent())
				->set_type(LoggedEventType::DEAUTHENTICATION)
				->set_card_id($card1_id)
				->set_equipment_id($equipment2_id)
				->set_time('2010-09-11 09:10:11')
		)->id();

		$eventId4 = $model->create(
			(new LoggedEvent())
				->set_type(LoggedEventType::DEAUTHENTICATION)
				->set_card_id($card1_id)
				->set_equipment_id($equipment1_id)
				->set_time('2010-09-12 10:11:12')
		)->id();

		$eventId5 = $model->create(
			(new LoggedEvent())
				->set_type(LoggedEventType::DEAUTHENTICATION)
				->set_card_id($card2_id)
				->set_equipment_id($equipment1_id)
				->set_time('2010-10-11 08:09:10')
		)->id();

		$eventId6 = $model->create(
			(new LoggedEvent())
				->set_type(LoggedEventType::DEAUTHENTICATION)
				->set_card_id($card2_id)
				->set_equipment_id($equipment2_id)
				->set_time('2010-10-11 09:10:11')
		)->id();

		$eventId7 = $model->create(
			(new LoggedEvent())
				->set_type(LoggedEventType::DEAUTHENTICATION)
				->set_card_id($card2_id)
				->set_equipment_id($equipment1_id)
				->set_time('2010-10-12 10:11:12')
		)->id();

		$badge_model = new BadgeModel(self::$config);
		$counts = $badge_model->countForUser(self::$user1->id());
		self::assertIsArray($counts);
		self::assertArrayHasKey(self::$type1->id(), $counts);
		self::assertSame(4, $counts[self::$type1->id()]);
		self::assertArrayHasKey(self::$type2->id(), $counts);
		self::assertSame(2, $counts[self::$type2->id()]);

		// drop to SQL to cleanup
		$statement = $model
			->configuration()
			->writable_db_connection()
			->prepare('DELETE FROM log WHERE id = ?');
		self::assertTrue($statement->execute([$eventId1]));
		self::assertTrue($statement->execute([$eventId2]));
		self::assertTrue($statement->execute([$eventId3]));
		self::assertTrue($statement->execute([$eventId4]));
		self::assertTrue($statement->execute([$eventId5]));
		self::assertTrue($statement->execute([$eventId6]));
		self::assertTrue($statement->execute([$eventId7]));
	}

	public function testCountForActiveUsers(): void {
		$model = new LoggedEventModel(self::$config);

		$card1_id = self::$card1->id();
		$card2_id = self::$card2->id();
		$card3_id = self::$card3->id();
		$card4_id = self::$card4->id();
		$equipment1_id = self::$equipment1->id();
		$equipment2_id = self::$equipment2->id();

		$eventId1 = $model->create(
			(new LoggedEvent())
				->set_type(LoggedEventType::STARTUP_COMPLETE)
				->set_equipment_id($equipment1_id)
				->set_time('2010-09-10 13:55:42')
		)->id();

		$eventId2 = $model->create(
			(new LoggedEvent())
				->set_type(LoggedEventType::DEAUTHENTICATION)
				->set_card_id($card1_id)
				->set_equipment_id($equipment1_id)
				->set_time('2010-09-11 08:09:10')
		)->id();

		$eventId3 = $model->create(
			(new LoggedEvent())
				->set_type(LoggedEventType::DEAUTHENTICATION)
				->set_card_id($card2_id)
				->set_equipment_id($equipment2_id)
				->set_time('2010-09-11 09:10:11')
		)->id();

		$eventId4 = $model->create(
			(new LoggedEvent())
				->set_type(LoggedEventType::DEAUTHENTICATION)
				->set_card_id($card3_id)
				->set_equipment_id($equipment1_id)
				->set_time('2010-10-12 10:11:12')
		)->id();

		$eventId5 = $model->create(
			(new LoggedEvent())
				->set_type(LoggedEventType::DEAUTHENTICATION)
				->set_card_id($card4_id)
				->set_equipment_id($equipment2_id)
				->set_time('2010-10-11 08:09:10')
		)->id();

		$eventId6 = $model->create(
			(new LoggedEvent())
				->set_type(LoggedEventType::DEAUTHENTICATION)
				->set_card_id($card2_id)
				->set_equipment_id($equipment1_id)
				->set_time('2010-10-11 09:10:11')
		)->id();

		$eventId7 = $model->create(
			(new LoggedEvent())
				->set_type(LoggedEventType::DEAUTHENTICATION)
				->set_card_id($card4_id)
				->set_equipment_id($equipment1_id)
				->set_time('2010-11-12 10:11:12')
		)->id();

		$eventId8 = $model->create(
			(new LoggedEvent())
				->set_type(LoggedEventType::DEAUTHENTICATION)
				->set_card_id($card3_id)
				->set_equipment_id($equipment1_id)
				->set_time('2010-11-11 08:09:10')
		)->id();

		$eventId9 = $model->create(
			(new LoggedEvent())
				->set_type(LoggedEventType::DEAUTHENTICATION)
				->set_card_id($card4_id)
				->set_equipment_id($equipment2_id)
				->set_time('2010-11-11 09:10:11')
		)->id();

		$badge_model = new BadgeModel(self::$config);
		$data = $badge_model->countForActiveUsers();
		self::assertIsArray($data);

		// rearrange data to make it easy to test
		$counts = [];
		foreach ($data as $datum) {
			self::assertArrayHasKey('count', $datum);
			self::assertArrayHasKey('equipment_type_id', $datum);
			self::assertArrayHasKey('user_id', $datum);
			self::assertArrayHasKey('name', $datum);
			self::assertArrayHasKey('email', $datum);

			$user_id = $datum['user_id'];
			if (!array_key_exists($user_id, $counts)) {
				$counts[$user_id] = [];
				$counts[$user_id]['name'] = $datum['name'];
				$counts[$user_id]['email'] = $datum['email'];
			}

			$counts[$user_id][$datum['equipment_type_id']] = $datum['count'];
		}

		// User one used equipment 1 twice and equipment two once
		self::assertArrayHasKey(self::$user1->id(), $counts);
		$user1Usage = $counts[self::$user1->id()];
		self::assertArrayHasKey('name', $user1Usage);
		self::assertSame(self::$user1->name(), $user1Usage['name']);
		self::assertArrayHasKey('email', $user1Usage);
		self::assertSame(self::$user1->email(), $user1Usage['email']);
		self::assertArrayHasKey(self::$type1->id(), $user1Usage);
		self::assertSame(2, $user1Usage[self::$type1->id()]);
		self::assertArrayHasKey(self::$type2->id(), $user1Usage);
		self::assertSame(1, $user1Usage[self::$type2->id()]);

		// We should not have data for the inactive user
		self::assertArrayNotHasKey(self::$user2->id(), $counts);

		// User three used equipment 1 once and equipment two twice
		self::assertArrayHasKey(self::$user3->id(), $counts);
		$user3Usage = $counts[self::$user3->id()];
		self::assertArrayHasKey('name', $user3Usage);
		self::assertSame(self::$user3->name(), $user3Usage['name']);
		self::assertArrayHasKey('email', $user3Usage);
		self::assertSame(self::$user3->email(), $user3Usage['email']);
		self::assertArrayHasKey(self::$type1->id(), $user3Usage);
		self::assertSame(1, $user3Usage[self::$type1->id()]);
		self::assertArrayHasKey(self::$type2->id(), $user3Usage);
		self::assertSame(2, $user3Usage[self::$type2->id()]);

		// drop to SQL to cleanup
		$statement = $model
			->configuration()
			->writable_db_connection()
			->prepare('DELETE FROM log WHERE id = ?');
		self::assertTrue($statement->execute([$eventId1]));
		self::assertTrue($statement->execute([$eventId2]));
		self::assertTrue($statement->execute([$eventId3]));
		self::assertTrue($statement->execute([$eventId4]));
		self::assertTrue($statement->execute([$eventId5]));
		self::assertTrue($statement->execute([$eventId6]));
		self::assertTrue($statement->execute([$eventId7]));
		self::assertTrue($statement->execute([$eventId8]));
		self::assertTrue($statement->execute([$eventId9]));
	}
}
