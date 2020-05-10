<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Portalbox\Config;
use Portalbox\Entity\Payment;
use Portalbox\Entity\Role;
use Portalbox\Entity\User;
use Portalbox\Model\PaymentModel;
use Portalbox\Model\UserModel;

final class PaymentModelTest extends TestCase {
	/**
	 * A user guananteed to exist in the DB
	 * @var User
	 */
	private $user;

	/**
	 * The configuration
	 * @var Config
	 */
	private $config;

	public function setUp(): void {
		parent::setUp();
		$this->config = Config::config();

		$model = new UserModel($this->config);

		$role_id = 3;	// default id of system defined admin role

		$role = (new Role())
			->set_id($role_id);

		$name = 'Tom Egan';
		$email = 'tom@ficticious.tld';
		$comment = 'Test Monkey';
		$active = TRUE;

		$user = (new User())
			->set_name($name)
			->set_email($email)
			->set_comment($comment)
			->set_is_active($active)
			->set_role($role);

		$this->user = $model->create($user);
	}

	public function tearDown() : void {
		$model = new UserModel($this->config);
		$model->delete($this->user->id());
	}

	public function testModel(): void {
		$model = new PaymentModel($this->config);

		$amount = '20.00';
		$time = '2020-04-18 20:12:34';

		$payment = (new Payment())
			->set_user_id($this->user->id())
			->set_amount($amount)
			->set_time($time);

		$payment_as_created = $model->create($payment);

		self::assertNotNull($payment_as_created);
		$payment_id = $payment_as_created->id();
		self::assertIsInt($payment_id);
		self::assertEquals($this->user->id(), $payment_as_created->user_id());
		self::assertEquals($amount, $payment_as_created->amount());
		self::assertEquals($time, $payment_as_created->time());

		$payment_as_found = $model->read($payment_id);

		self::assertNotNull($payment_as_found);
		self::assertEquals($payment_id, $payment_as_found->id());
		self::assertEquals($this->user->id(), $payment_as_found->user_id());
		self::assertEquals($amount, $payment_as_found->amount());
		self::assertEquals($time, $payment_as_found->time());

		$amount = '25.50';
		$time = '2020-04-01 00:00:00';
		$payment_as_found->set_amount($amount);
		$payment_as_found->set_time($time);

		$payment_as_modified = $model->update($payment_as_found);

		self::assertNotNull($payment_as_modified);
		self::assertEquals($payment_id, $payment_as_modified->id());
		self::assertEquals($this->user->id(), $payment_as_modified->user_id());
		self::assertEquals($amount, $payment_as_modified->amount());
		self::assertEquals($time, $payment_as_modified->time());

		$payment_as_deleted = $model->delete($payment_id);

		self::assertNotNull($payment_as_deleted);
		self::assertEquals($payment_id, $payment_as_deleted->id());
		self::assertEquals($this->user->id(), $payment_as_deleted->user_id());
		self::assertEquals($amount, $payment_as_deleted->amount());
		self::assertEquals($time, $payment_as_deleted->time());

		$payment_as_not_found = $model->read($payment_id);

		self::assertNull($payment_as_not_found);
	}
}