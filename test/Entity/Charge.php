<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Portalbox\Entity\Charge;

final class ChargeTest extends TestCase {
	public function testAgreement(): void {
		$id = 42;
		$equipment_id = 6;
		$user_id = 7;
		$amount = '2.00';
		$time = '2020-04-18 20:12:34';
		$charge_policy_id = 1;
		$charge_rate = '0.05';
		$charged_time = 40;

		$charge = (new Charge())
			->set_id($id)
			->set_equipment_id($equipment_id)
			->set_user_id($user_id)
			->set_amount($amount)
			->set_time($time)
			->set_charge_policy_id($charge_policy_id)
			->set_charge_rate($charge_rate)
			->set_charged_time($charged_time);

		self::assertEquals($id, $charge->id());
		self::assertEquals($equipment_id, $charge->equipment_id());
		self::assertEquals($user_id, $charge->user_id());
		self::assertEquals($amount, $charge->amount());
		self::assertEquals($time, $charge->time());
		self::assertEquals($charge_policy_id, $charge->charge_policy_id());
		self::assertEquals($charge_rate, $charge->charge_rate());
		self::assertEquals($charged_time, $charge->charged_time());
	}
}