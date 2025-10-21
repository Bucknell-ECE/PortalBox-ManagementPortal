<?php

declare(strict_types=1);

namespace Test\Portalbox\Model;

use PHPUnit\Framework\TestCase;
use Portalbox\Config;
use Portalbox\Entity\ChargePolicy;
use Portalbox\Entity\Equipment;
use Portalbox\Entity\EquipmentType;
use Portalbox\Entity\Location;
use Portalbox\Entity\LoggedEvent;
use Portalbox\Entity\LoggedEventType;
use Portalbox\Entity\ShutdownCard;
use Portalbox\Model\CardModel;
use Portalbox\Model\EquipmentModel;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Model\LocationModel;
use Portalbox\Model\LoggedEventModel;
use Portalbox\Query\LoggedEventQuery;

/**
 * Test LoggedEvent Model... because this model is read only we need to
 * be creative to test it. Therefore we will create Logged event using
 * the underlying connection then the model to read them before using
 * the underlying connection again to delete them.
 */
final class LoggedEventModelTest extends TestCase {
	/**
	 * A location that exists in the db
	 */
	private static Location $location;

	/**
	 * An equipment type which exists in the db
	 */
	private static EquipmentType $type;

	/**
	 * The equipment connected to a portalbox
	 */
	private static Equipment $equipment1;
	private static Equipment $equipment2;

	/**
	 * A card we can use as test data
	 */
	private static ShutdownCard $card;

	/**
	 * The configuration
	 */
	private static Config $config;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		self::$config = Config::config();

		// provision a location in the db
		$model = new LocationModel(self::$config);

		$name = 'Robotics Shop';

		self::$location = $model->create(
			(new Location())
				->set_name($name)
		);

		// provision an equipment type in the db
		$model = new EquipmentTypeModel(self::$config);

		$name = 'Floodlight';
		$requires_training = false;
		$charge_policy_id = ChargePolicy::NO_CHARGE;

		self::$type = $model->create(
			(new EquipmentType())
				->set_name($name)
				->set_requires_training($requires_training)
				->set_charge_policy_id($charge_policy_id)
				->set_allow_proxy(false)
		);

		// provision a piece of equipment in the db
		$model = new EquipmentModel(self::$config);

		$name = '1000W Floodlight';
		$mac_address1 = '0123456789ab';
		$mac_address2 = '0123456789ac';
		$timeout = 0;
		$is_in_service = true;
		$service_minutes = 500;

		self::$equipment1 = $model->create(
			(new Equipment())
				->set_name($name)
				->set_type(self::$type)
				->set_location(self::$location)
				->set_mac_address($mac_address1)
				->set_timeout($timeout)
				->set_is_in_service($is_in_service)
				->set_service_minutes($service_minutes)
		);

		self::$equipment2 = $model->create(
			(new Equipment())
				->set_name($name)
				->set_type(self::$type)
				->set_location(self::$location)
				->set_mac_address($mac_address2)
				->set_timeout($timeout)
				->set_is_in_service($is_in_service)
				->set_service_minutes($service_minutes)
		);

		$model = new CardModel(self::$config);

		$card_id = 9812347165;

		self::$card = $model->create(
			(new ShutdownCard())
				->set_id($card_id)
		);
	}

	public static function tearDownAfterClass(): void {
		$model = new CardModel(self::$config);
		$model->delete(self::$card->id());

		$model = new EquipmentModel(self::$config);
		$model->delete(self::$equipment1->id());
		$model->delete(self::$equipment2->id());

		$model = new EquipmentTypeModel(self::$config);
		$model->delete(self::$type->id());

		$model = new LocationModel(self::$config);
		$model->delete(self::$location->id());

		parent::tearDownAfterClass();
	}

	public function testCreateReadEventWithoutCard(): void {
		$model = new LoggedEventModel(self::$config);

		$event_type_id = LoggedEventType::STARTUP_COMPLETE;
		$equipment1_id = self::$equipment1->id();
		$time = '2025-02-28 13:55:42';

		$event = $model->create(
			(new LoggedEvent())
				->set_type_id($event_type_id)
				->set_equipment_id($equipment1_id)
				->set_time($time)
		);

		self::assertInstanceOf(LoggedEvent::class, $event);
		$id = $event->id();
		self::assertNotNull($id);
		self::assertSame($event_type_id, $event->type_id());
		self::assertNull($event->card_id());
		self::assertSame($equipment1_id, $event->equipment_id());
		self::assertSame($time, $event->time());

		$event = $model->read($id);

		self::assertInstanceOf(LoggedEvent::class, $event);
		self::assertSame($event_type_id, $event->type_id());
		self::assertNull($event->card_id());
		self::assertSame($equipment1_id, $event->equipment_id());
		self::assertSame($time, $event->time());

		// drop to SQL to cleanup
		$statement = $model
			->configuration()
			->writable_db_connection()
			->prepare('DELETE FROM log WHERE id = ?');
		self::assertTrue($statement->execute([$id]));

		self::assertNull($model->read($id));
	}

	public function testCreateReadEventWithCard(): void {
		$model = new LoggedEventModel(self::$config);

		$event_type_id = LoggedEventType::PLANNED_SHUTDOWN;
		$card_id = self::$card->id();
		$equipment1_id = self::$equipment1->id();
		$time = '2025-02-28 13:55:42';

		$event = $model->create(
			(new LoggedEvent())
				->set_type_id($event_type_id)
				->set_card_id($card_id)
				->set_equipment_id($equipment1_id)
				->set_time($time)
		);

		self::assertInstanceOf(LoggedEvent::class, $event);
		$id = $event->id();
		self::assertNotNull($id);
		self::assertSame($event_type_id, $event->type_id());
		self::assertSame($card_id, $event->card_id());
		self::assertSame($equipment1_id, $event->equipment_id());
		self::assertSame($time, $event->time());

		$event = $model->read($id);

		self::assertInstanceOf(LoggedEvent::class, $event);
		self::assertSame($event_type_id, $event->type_id());
		self::assertSame($card_id, $event->card_id());
		self::assertSame($equipment1_id, $event->equipment_id());
		self::assertSame($time, $event->time());

		// drop to SQL to cleanup
		$statement = $model
			->configuration()
			->writable_db_connection()
			->prepare('DELETE FROM log WHERE id = ?');
		self::assertTrue($statement->execute([$id]));

		self::assertNull($model->read($id));
	}

	public function testSearch(): void {
		$model = new LoggedEventModel(self::$config);

		$bad_card_id = 1234567890;
		$card_id = self::$card->id();
		$equipment1_id = self::$equipment1->id();
		$equipment2_id = self::$equipment2->id();

		$eventId1 = $model->create(
			(new LoggedEvent())
				->set_type_id(LoggedEventType::STARTUP_COMPLETE)
				->set_equipment_id($equipment2_id)
				->set_time('2025-02-28 13:55:42')
		)->id();

		$eventId2 = $model->create(
			(new LoggedEvent())
				->set_type_id(LoggedEventType::UNSUCCESSFUL_AUTHENTICATION)
				->set_card_id($bad_card_id)
				->set_equipment_id($equipment1_id)
				->set_time('2025-09-11 09:10:11')
		)->id();

		$eventId3 = $model->create(
			(new LoggedEvent())
				->set_type_id(LoggedEventType::UNSUCCESSFUL_AUTHENTICATION)
				->set_card_id($bad_card_id)
				->set_equipment_id($equipment2_id)
				->set_time('2025-09-11 09:10:12')
		)->id();

		$eventId4 = $model->create(
			(new LoggedEvent())
				->set_type_id(LoggedEventType::UNSUCCESSFUL_AUTHENTICATION)
				->set_card_id($bad_card_id)
				->set_equipment_id($equipment1_id)
				->set_time('2025-09-11 09:10:13')
		)->id();

		$eventId5 = $model->create(
			(new LoggedEvent())
				->set_type_id(LoggedEventType::PLANNED_SHUTDOWN)
				->set_card_id($card_id)
				->set_equipment_id($equipment2_id)
				->set_time('2025-09-12 10:15:20')
		)->id();

		$events = array_map(fn($event) => $event->id(), $model->search());
		self::assertContains($eventId1, $events);
		self::assertContains($eventId2, $events);
		self::assertContains($eventId3, $events);
		self::assertContains($eventId4, $events);
		self::assertContains($eventId5, $events);

		$query = new LoggedEventQuery();
		$events = array_map(fn($event) => $event->id(), $model->search($query));
		self::assertContains($eventId1, $events);
		self::assertContains($eventId2, $events);
		self::assertContains($eventId3, $events);
		self::assertContains($eventId4, $events);
		self::assertContains($eventId5, $events);

		// check that we can query by event type
		$query = (new LoggedEventQuery())
			->set_type_id(LoggedEventType::UNSUCCESSFUL_AUTHENTICATION);
		$events = array_map(fn($event) => $event->id(), $model->search($query));
		self::assertNotContains($eventId1, $events);
		self::assertContains($eventId2, $events);
		self::assertContains($eventId3, $events);
		self::assertContains($eventId4, $events);
		self::assertNotContains($eventId5, $events);

		// check that we can query by equipment
		$query = (new LoggedEventQuery())
			->set_equipment_id($equipment2_id);
		$events = array_map(fn($event) => $event->id(), $model->search($query));
		self::assertContains($eventId1, $events);
		self::assertNotContains($eventId2, $events);
		self::assertContains($eventId3, $events);
		self::assertNotContains($eventId4, $events);
		self::assertContains($eventId5, $events);

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
	}

	public function testCount(): void {
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

		$eventId5 = $model->create(
			(new LoggedEvent())
				->set_type_id(LoggedEventType::PLANNED_SHUTDOWN)
				->set_card_id($card_id)
				->set_equipment_id($equipment1_id)
				->set_time('2010-09-12 10:15:20')
		)->id();

		$eventId6 = $model->create(
			(new LoggedEvent())
				->set_type_id(LoggedEventType::STARTUP_COMPLETE)
				->set_equipment_id($equipment1_id)
				->set_time('2010-09-12 13:55:42')
		)->id();

		$eventId7 = $model->create(
			(new LoggedEvent())
				->set_type_id(LoggedEventType::SUCCESSFUL_AUTHENTICATION)
				->set_card_id($card_id)
				->set_equipment_id($equipment1_id)
				->set_time('2010-09-13 08:09:10')
		)->id();

		$eventId8 = $model->create(
			(new LoggedEvent())
				->set_type_id(LoggedEventType::SUCCESSFUL_AUTHENTICATION)
				->set_card_id($card_id)
				->set_equipment_id($equipment1_id)
				->set_time('2010-09-14 09:10:11')
		)->id();

		$eventId9 = $model->create(
			(new LoggedEvent())
				->set_type_id(LoggedEventType::SUCCESSFUL_AUTHENTICATION)
				->set_card_id($card_id)
				->set_equipment_id($equipment1_id)
				->set_time('2010-09-14 10:11:12')
		)->id();

		// With no query we could get results from the running system
		// so we just check the events we added before the system existed
		$counts = $model->count();
		self::assertArrayHasKey('2010-09-11', $counts);
		self::assertSame(2, $counts['2010-09-11']);
		self::assertArrayHasKey('2010-09-12', $counts);
		self::assertSame(1, $counts['2010-09-12']);
		self::assertArrayHasKey('2010-09-13', $counts);
		self::assertSame(1, $counts['2010-09-13']);
		self::assertArrayHasKey('2010-09-14', $counts);
		self::assertSame(2, $counts['2010-09-14']);

		$query = new LoggedEventQuery();
		$counts = $model->count($query);
		self::assertArrayHasKey('2010-09-11', $counts);
		self::assertSame(2, $counts['2010-09-11']);
		self::assertArrayHasKey('2010-09-12', $counts);
		self::assertSame(1, $counts['2010-09-12']);
		self::assertArrayHasKey('2010-09-13', $counts);
		self::assertSame(1, $counts['2010-09-13']);
		self::assertArrayHasKey('2010-09-14', $counts);
		self::assertSame(2, $counts['2010-09-14']);

		// check that we can query before a date
		$query = (new LoggedEventQuery())
			->set_on_or_before('2010-09-12 23:59:59');
		$counts = $model->count($query);
		self::assertArrayHasKey('2010-09-11', $counts);
		self::assertSame(2, $counts['2010-09-11']);
		self::assertArrayHasKey('2010-09-12', $counts);
		self::assertSame(1, $counts['2010-09-12']);
		self::assertArrayNotHasKey('2010-09-13', $counts);
		self::assertArrayNotHasKey('2010-09-14', $counts);

		// check that we can query after a date
		$query = (new LoggedEventQuery())
			->set_on_or_after('2010-09-13');
		$counts = $model->count($query);
		self::assertArrayNotHasKey('2010-09-11', $counts);
		self::assertArrayNotHasKey('2010-09-12', $counts);
		self::assertArrayHasKey('2010-09-13', $counts);
		self::assertSame(1, $counts['2010-09-13']);
		self::assertArrayHasKey('2010-09-14', $counts);
		self::assertSame(2, $counts['2010-09-14']);

		// check that we can query by equipment id
		$query = (new LoggedEventQuery())
			->set_equipment_id($equipment1_id);
		$counts = $model->count($query);
		self::assertArrayHasKey('2010-09-11', $counts);
		self::assertSame(1, $counts['2010-09-11']);
		self::assertArrayHasKey('2010-09-12', $counts);
		self::assertSame(1, $counts['2010-09-12']);
		self::assertArrayHasKey('2010-09-13', $counts);
		self::assertSame(1, $counts['2010-09-13']);
		self::assertArrayHasKey('2010-09-14', $counts);
		self::assertSame(2, $counts['2010-09-14']);

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
