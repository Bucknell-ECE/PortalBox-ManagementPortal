<?php

declare(strict_types=1);

namespace Test\Portalbox\Transform;

use PHPUnit\Framework\TestCase;
use Portalbox\Transform\PaymentTransformer;
use Portalbox\Type\Payment;

final class PaymentTransformerTest extends TestCase {
	public function testSerialize(): void {
		$transformer = new PaymentTransformer();

		$id = 42;
		$user_id = 2;
		$amount = '29.95';
		$time = '2020-05-30 21:45:34';

		$payment = (new Payment())
			->set_id($id)
			->set_user_id($user_id)
			->set_amount($amount)
			->set_time($time);

		$data = $transformer->serialize($payment, true);

		self::assertNotNull($data);
		self::assertArrayHasKey('id', $data);
		self::assertEquals($id, $data['id']);
		self::assertArrayHasKey('user_id', $data);
		self::assertEquals($user_id, $data['user_id']);
		self::assertArrayHasKey('amount', $data);
		self::assertEquals($amount, $data['amount']);
		self::assertArrayHasKey('time', $data);
		self::assertEquals($time, $data['time']);
	}
}
