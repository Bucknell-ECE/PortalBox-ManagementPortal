<?php

declare(strict_types=1);

namespace Test\Portalbox\Transform;

use PHPUnit\Framework\TestCase;
use Portalbox\Enumeration\ChargePolicy;
use Portalbox\Transform\ChargeTransformer;
use Portalbox\Type\Charge;
use Portalbox\Type\Equipment;
use Portalbox\Type\User;

final class ChargeTransformerTest extends TestCase {
	public function testSerialize(): void {
		$user_id = 501;
		$user_name = 'Tom Egan';

		$user = (new User())
			->set_id($user_id)
			->set_name($user_name);

		$equipment_id = 12;
		$equipment_name = '1000W Floodlight';

		$equipment = (new Equipment())
			->set_id($equipment_id)
			->set_name($equipment_name);

		$id = 42;
		$amount = '2.00';
		$charge_policy = ChargePolicy::PER_USE;
		$charge_rate = '2.00';
		$charged_time = 25;
		$time = '2020-05-31 10:46:34';

		$charge = (new Charge())
			->set_id($id)
			->set_user($user)
			->set_equipment($equipment)
			->set_amount($amount)
			->set_charge_policy($charge_policy)
			->set_charge_rate($charge_rate)
			->set_charged_time($charged_time)
			->set_time($time);

		$transformer = new ChargeTransformer();

		$data = $transformer->serialize($charge);

		self::assertNotNull($data);
		self::assertArrayHasKey('id', $data);
		self::assertEquals($id, $data['id']);
		self::assertArrayHasKey('equipment_id', $data);
		self::assertEquals($equipment_id, $data['equipment_id']);
		self::assertArrayHasKey('equipment', $data);
		self::assertEquals($equipment_name, $data['equipment']);
		self::assertArrayHasKey('user_id', $data);
		self::assertEquals($user_id, $data['user_id']);
		self::assertArrayHasKey('user', $data);
		self::assertEquals($user_name, $data['user']);
		self::assertArrayHasKey('amount', $data);
		self::assertEquals($amount, $data['amount']);
		self::assertArrayHasKey('charge_policy', $data);
		self::assertEquals($charge_policy->value, $data['charge_policy']);
		self::assertArrayHasKey('charge_rate', $data);
		self::assertEquals($charge_rate, $data['charge_rate']);
		self::assertArrayHasKey('charged_time', $data);
		self::assertEquals($charged_time, $data['charged_time']);
		self::assertArrayHasKey('time', $data);
		self::assertEquals($time, $data['time']);
	}
}
