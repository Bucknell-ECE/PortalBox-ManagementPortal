<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Portalbox\Entity\UserCard;
use Portalbox\Entity\Equipment;
use Portalbox\Entity\Location;
use Portalbox\Entity\LoggedEvent;
use Portalbox\Entity\LoggedEventType;
use Portalbox\Entity\User;

final class LoggedEventTest extends TestCase {
	public function testAgreement(): void {
		$id = 981726354;
		$time = '2020-05-10 09:54:12';
		$type_id = LoggedEventType::SUCCESSFUL_AUTHENTICATION;
		$equipment_id = 42;
		$equipment_type_id = 3;
		$equipment_type = 'Arc Welder';
		$card_id = 1234;
		$user_id = 137;

		$event = (new LoggedEvent())
			->set_id($id)
			->set_time($time)
			->set_type_id($type_id)
			->set_equipment_id($equipment_id)
			->set_equipment_type_id($equipment_type_id)
			->set_equipment_type($equipment_type)
			->set_card_id($card_id)
			->set_user_id($user_id);

		self::assertEquals($id, $event->id());
		self::assertEquals($time, $event->time());
		self::assertEquals($type_id, $event->type_id());
		self::assertEquals(
			LoggedEventType::name_for_type($type_id),
			$event->type()
		);
		self::assertEquals($equipment_id, $event->equipment_id());
		self::assertEquals('', $event->equipment_name());
		self::assertNull($event->equipment());
		self::assertEquals('', $event->location_name());
		self::assertEquals($equipment_type_id, $event->equipment_type_id());
		self::assertEquals($equipment_type, $event->equipment_type());
		self::assertEquals($card_id, $event->card_id());
		self::assertNull($event->card());
		self::assertEquals($user_id, $event->user_id());
		self::assertEquals('', $event->user_name());
		self::assertNull($event->user());
	}

	public function testJoinedDataAgreement(): void {
		$user_id = 12;
		$user_name = 'Jane Doe';
		$user = (new User())
			->set_id($user_id)
			->set_name($user_name);

		$location_id = 137;
		$location_name = 'Moon Base Alpha';
		$location = (new Location())
			->set_id($location_id)
			->set_name($location_name);

		$equipment_id = 34;
		$equipment_name = 'Arc Welder';
		$equipment = (new Equipment())
			->set_id($equipment_id)
			->set_name($equipment_name)
			->set_location($location);

		$card_id = 2;
		$card = (new UserCard())
			->set_id($card_id);

		$event = (new LoggedEvent())
			->set_card($card)
			->set_equipment($equipment)
			->set_user($user);

		self::assertEquals($equipment_id, $event->equipment_id());
		self::assertEquals($equipment_name, $event->equipment_name());
		self::assertEquals($equipment, $event->equipment());
		self::assertEquals($location_name, $event->location_name());
		self::assertEquals($card_id, $event->card_id());
		self::assertEquals($card, $event->card());
		self::assertEquals($user_id, $event->user_id());
		self::assertEquals($user, $event->user());
	}
}
