<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Portalbox\Entity\Payment;

final class PaymentTest extends TestCase {
	public function testAgreement(): void {
		$id = 42;
		$user_id = 7;
		$amount = '20.00';
		$time = '2020-04-18 20:12:34';

		$payment = (new Payment())
			->set_id($id)
			->set_user_id($user_id)
			->set_amount($amount)
			->set_time($time);

		self::assertEquals($id, $payment->id());
		self::assertEquals($user_id, $payment->user_id());
		self::assertEquals($amount, $payment->amount());
		self::assertEquals($time, $payment->time());
	}
}