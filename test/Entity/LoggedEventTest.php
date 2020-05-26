<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Portalbox\Entity\LoggedEvent;
use Portalbox\Entity\LoggedEventType;

final class LoggedEventTest extends TestCase {
	public function testAgreement(): void {
		$id = 981726354;
		$time = '2020-05-10 09:54:12';
		$type_id = LoggedEventType::SUCESSFUL_AUTHENTICATION;
		$equipment_id = 42;
		// $equipment = ???;
		$card_id = 1234;
		// $card = ???;
		$user_id = 137;
		// $user = ???;

		$event = (new LoggedEvent())
			->set_id($id)
			->set_time($time)
			->set_type_id($type_id)
			->set_equipment_id($equipment_id)
			->set_card_id($card_id)
			->set_user_id($user_id);

		self::assertEquals($id, $event->id());
		self::assertEquals($time, $event->time());
		self::assertEquals($type_id, $event->type_id());
		self::assertEquals($equipment_id, $event->equipment_id());
		self::assertEquals($card_id, $event->card_id());
		self::assertEquals($user_id, $event->user_id());
	}
}