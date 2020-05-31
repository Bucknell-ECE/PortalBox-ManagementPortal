<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use PortalBox\Entity\Payment;
use Portalbox\Transform\PaymentTransformer;

final class PaymentTransformerTest extends TestCase {
	public function testDeserialize(): void {
		$transformer = new PaymentTransformer();

		$id = 42;
		$user_id = 137;
		$amount = '29.95';
		$time = '2020-05-30 21:45:34';

		$data = [
			'id' => $id,
			'user_id' => $user_id,
			'amount' => $amount,
			'time' => $time
		];

		$payment = $transformer->deserialize($data);

		self::assertNotNull($payment);
		self::assertNull($payment->id());
		self::assertEquals($user_id, $payment->user_id());
		self::assertEquals($amount, $payment->amount());
		self::assertEquals($time, $payment->time());
	}

	public function testDeserializeInvalidDataUserID(): void {
		$transformer = new PaymentTransformer();

		$id = 42;
		$amount = '29.95';
		$time = '2020-05-30 21:45:34';

		$data = [
			'id' => $id,
			'amount' => $amount,
			'time' => $time
		];

		$this->expectException(InvalidArgumentException::class);
		$payment = $transformer->deserialize($data);
	}

	public function testDeserializeInvalidDataTime(): void {
		$transformer = new PaymentTransformer();

		$id = 42;
		$user_id = 137;
		$amount = '29.95';

		$data = [
			'id' => $id,
			'user_id' => $user_id,
			'amount' => $amount
		];

		$this->expectException(InvalidArgumentException::class);
		$payment = $transformer->deserialize($data);
	}

	public function testDeserializeInvalidDataAmount(): void {
		$transformer = new PaymentTransformer();

		$id = 42;
		$user_id = 137;
		$time = '2020-05-30 21:45:34';

		$data = [
			'id' => $id,
			'user_id' => $user_id,
			'time' => $time
		];

		$this->expectException(InvalidArgumentException::class);
		$payment = $transformer->deserialize($data);
	}

	public function testSerialize(): void {
		$transformer = new PaymentTransformer();

		$id = 42;
		$user_id = 137;
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