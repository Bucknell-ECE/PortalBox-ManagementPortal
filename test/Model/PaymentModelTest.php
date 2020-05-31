<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Portalbox\Config;
use Portalbox\Entity\Payment;
use Portalbox\Entity\Role;
use Portalbox\Entity\User;
use Portalbox\Model\PaymentModel;
use Portalbox\Model\UserModel;
use Portalbox\Query\PaymentQuery;

final class PaymentModelTest extends TestCase {
	/**
	 * A user guananteed to exist in the DB
	 * @var User
	 */
	private static $user;

	/**
	 * The configuration
	 * @var Config
	 */
	private static $config;

	public static function setUpBeforeClass(): void {
		parent::setUp();
		self::$config = Config::config();

		$model = new UserModel(self::$config);

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

		self::$user = $model->create($user);
	}

	public static function tearDownAfterClass() : void {
		$model = new UserModel(self::$config);
		$model->delete(self::$user->id());
	}

	public function testModel(): void {
		$model = new PaymentModel(self::$config);

		$amount = '20.00';
		$time = '2020-04-18 20:12:34';

		$payment = (new Payment())
			->set_user_id(self::$user->id())
			->set_amount($amount)
			->set_time($time);

		$payment_as_created = $model->create($payment);

		self::assertNotNull($payment_as_created);
		$payment_id = $payment_as_created->id();
		self::assertIsInt($payment_id);
		self::assertEquals(self::$user->id(), $payment_as_created->user_id());
		self::assertEquals($amount, $payment_as_created->amount());
		self::assertEquals($time, $payment_as_created->time());

		$payment_as_found = $model->read($payment_id);

		self::assertNotNull($payment_as_found);
		self::assertEquals($payment_id, $payment_as_found->id());
		self::assertEquals(self::$user->id(), $payment_as_found->user_id());
		self::assertEquals($amount, $payment_as_found->amount());
		self::assertEquals($time, $payment_as_found->time());

		$amount = '25.50';
		$time = '2020-04-01 00:00:00';
		$payment_as_found->set_amount($amount);
		$payment_as_found->set_time($time);

		$payment_as_modified = $model->update($payment_as_found);

		self::assertNotNull($payment_as_modified);
		self::assertEquals($payment_id, $payment_as_modified->id());
		self::assertEquals(self::$user->id(), $payment_as_modified->user_id());
		self::assertEquals($amount, $payment_as_modified->amount());
		self::assertEquals($time, $payment_as_modified->time());

		$payment_as_deleted = $model->delete($payment_id);

		self::assertNotNull($payment_as_deleted);
		self::assertEquals($payment_id, $payment_as_deleted->id());
		self::assertEquals(self::$user->id(), $payment_as_deleted->user_id());
		self::assertEquals($amount, $payment_as_deleted->amount());
		self::assertEquals($time, $payment_as_deleted->time());

		$payment_as_not_found = $model->read($payment_id);

		self::assertNull($payment_as_not_found);
	}

	public function testSearch(): void {
		$model = new PaymentModel(self::$config);

		$amount = '20.00';
		$time = '2020-04-18 20:12:34';

		$payment = (new Payment())
			->set_user_id(self::$user->id())
			->set_amount($amount)
			->set_time($time);

		$payment_as_created = $model->create($payment);

		$payment_id = $payment_as_created->id();

		$query = new PaymentQuery();
		$all_payments = $model->search($query);

		self::assertIsArray($all_payments);
		self::assertNotEmpty($all_payments);
		self::assertContainsOnlyInstancesOf(Payment::class, $all_payments);

		$query = (new PaymentQuery())->set_user_id(self::$user->id());
		$users_payments = $model->search($query);

		self::assertIsArray($users_payments);
		self::assertNotEmpty($users_payments);
		self::assertContainsOnlyInstancesOf(Payment::class, $users_payments);

		$model->delete($payment_id);
	}
}