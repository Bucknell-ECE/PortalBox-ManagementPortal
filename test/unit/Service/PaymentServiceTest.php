<?php

declare(strict_types=1);

namespace Test\Portalbox\Service;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Portalbox\Enumeration\Permission;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Exception\NotFoundException;
use Portalbox\Model\PaymentModel;
use Portalbox\Model\UserModel;
use Portalbox\Query\PaymentQuery;
use Portalbox\Service\PaymentService;
use Portalbox\Session;
use Portalbox\Type\Payment;
use Portalbox\Type\Role;
use Portalbox\Type\User;

final class PaymentServiceTest extends TestCase {
	#region test create()

	public function testCreateThrowsWhenNotAuthenticated() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$paymentModel = $this->createStub(PaymentModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new PaymentService(
			$session,
			$paymentModel,
			$userModel
 		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(PaymentService::ERROR_UNAUTHENTICATED_CREATE);
		$service->create('not a file path');
	}

	public function testCreateThrowsWhenNotAuthorized() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$paymentModel = $this->createStub(PaymentModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new PaymentService(
			$session,
			$paymentModel,
			$userModel
 		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(PaymentService::ERROR_UNAUTHORIZED_CREATE);
		$service->create('not a file path');
	}

	public function testCreateThrowsWhenFileIsNotReadable() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_PAYMENT])
				)
		);

		$paymentModel = $this->createStub(PaymentModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new PaymentService(
			$session,
			$paymentModel,
			$userModel
 		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(PaymentService::ERROR_INVALID_PAYMENT_DATA);
		// PHP warning is intentionally suppressed in next line for testing
		@$service->create('file_does_not_exist.json');
	}

	public function testCreateThrowsWhenDataIsNotArray() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_PAYMENT])
				)
		);

		$paymentModel = $this->createStub(PaymentModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new PaymentService(
			$session,
			$paymentModel,
			$userModel
 		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(PaymentService::ERROR_INVALID_PAYMENT_DATA);
		$service->create(realpath(__DIR__ . '/PaymentServiceTestData/DataIsNotArray.json'));
	}

	public function testCreateThrowsWhenUserNotSpecified() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_PAYMENT])
				)
		);

		$paymentModel = $this->createStub(PaymentModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new PaymentService(
			$session,
			$paymentModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(PaymentService::ERROR_USER_ID_IS_REQUIRED);
		$service->create(realpath(__DIR__ . '/PaymentServiceTestData/UserNotSpecified.json'));
	}

	public function testCreateThrowsWhenUserIdIsNotInt() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_PAYMENT])
				)
		);

		$paymentModel = $this->createStub(PaymentModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new PaymentService(
			$session,
			$paymentModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(PaymentService::ERROR_USER_ID_IS_REQUIRED);
		$service->create(realpath(__DIR__ . '/PaymentServiceTestData/UserIdIsNotInt.json'));
	}

	public function testCreateThrowsWhenUserIsInvalid() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_PAYMENT])
				)
		);

		$paymentModel = $this->createStub(PaymentModel::class);

		$userModel = $this->createStub(UserModel::class);
		$userModel->method('read')->willReturn(null);

		$service = new PaymentService(
			$session,
			$paymentModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(PaymentService::ERROR_INVALID_USER_ID);
		$service->create(realpath(__DIR__ . '/PaymentServiceTestData/ValidPayment.json'));
	}

	public function testCreateThrowsWhenAmountIsNotSpecified() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_PAYMENT])
				)
		);

		$paymentModel = $this->createStub(PaymentModel::class);

		$userModel = $this->createStub(UserModel::class);
		$userModel->method('read')->willReturn(new User());

		$service = new PaymentService(
			$session,
			$paymentModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(PaymentService::ERROR_INVALID_AMOUNT);
		$service->create(realpath(__DIR__ . '/PaymentServiceTestData/AmountIsNotSpecified.json'));
	}

	public function testCreateThrowsWhenAmountIsNotDecimal() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_PAYMENT])
				)
		);

		$paymentModel = $this->createStub(PaymentModel::class);

		$userModel = $this->createStub(UserModel::class);
		$userModel->method('read')->willReturn(new User());

		$service = new PaymentService(
			$session,
			$paymentModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(PaymentService::ERROR_INVALID_AMOUNT);
		$service->create(realpath(__DIR__ . '/PaymentServiceTestData/AmountIsNotDecimal.json'));
	}

	public function testCreateThrowsWhenTimeIsNotSpecified() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_PAYMENT])
				)
		);

		$paymentModel = $this->createStub(PaymentModel::class);

		$userModel = $this->createStub(UserModel::class);
		$userModel->method('read')->willReturn(new User());

		$service = new PaymentService(
			$session,
			$paymentModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(PaymentService::ERROR_TIME_IS_REQUIRED);
		$service->create(realpath(__DIR__ . '/PaymentServiceTestData/TimeIsNotSpecified.json'));
	}

	public function testCreateThrowsWhenTimeIsInvalid() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_PAYMENT])
				)
		);

		$paymentModel = $this->createStub(PaymentModel::class);

		$userModel = $this->createStub(UserModel::class);
		$userModel->method('read')->willReturn(new User());

		$service = new PaymentService(
			$session,
			$paymentModel,
			$userModel
		);

		self::expectException(Exception::class);
		// we rely on PHP's exception message which can change without notice so no assertion
		$service->create(realpath(__DIR__ . '/PaymentServiceTestData/TimeIsInvalid.json'));
	}

	public function testCreateSuccess() {
		// values matching the input file
		$user_id = 2;
		$time = "2026-03-25 10:10:23";
		$amount = "23.56";

		$user = (new User())->set_id($user_id);

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_PAYMENT])
				)
		);

		$paymentModel = $this->createMock(PaymentModel::class);
		$paymentModel->expects($this->once())->method('create')->with(
			$this->callback(
				fn(Payment $payment) =>
					$payment->user() === $user
					&& $payment->amount() === $amount
					&& $payment->time() === $time
			)
		)
		->willReturnArgument(0);

		$userModel = $this->createStub(UserModel::class);
		$userModel->method('read')->willReturn($user);

		$service = new PaymentService(
			$session,
			$paymentModel,
			$userModel
		);

		$payment = $service->create(realpath(__DIR__ . '/PaymentServiceTestData/ValidPayment.json'));

		self::assertInstanceOf(Payment::class, $payment);
		self::assertSame($user, $payment->user());
		self::assertSame($amount, $payment->amount());
		self::assertSame($time, $payment->time());
	}

	#endregion test create()

	#region test read()

	public function testReadThrowsWhenNotAuthenticated() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$paymentModel = $this->createStub(PaymentModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new PaymentService(
			$session,
			$paymentModel,
			$userModel
 		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(PaymentService::ERROR_UNAUTHENTICATED_READ);
		$service->read(19);
	}

	public function testReadThrowsWhenNotAuthorized() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$paymentModel = $this->createStub(PaymentModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new PaymentService(
			$session,
			$paymentModel,
			$userModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(PaymentService::ERROR_UNAUTHORIZED_READ);
		$service->read(19);
	}

	public function testReadThrowsWhenPaymentDoesNotExist() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::READ_PAYMENT])
				)
		);

		$paymentModel = $this->createStub(PaymentModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new PaymentService(
			$session,
			$paymentModel,
			$userModel
		);

		self::expectException(NotFoundException::class);
		self::expectExceptionMessage(PaymentService::ERROR_PAYMENT_NOT_FOUND);
		$service->read(19);
	}

	public function testReadSuccess() {
		$payment = new Payment();

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::READ_PAYMENT])
				)
		);

		$paymentModel = $this->createStub(PaymentModel::class);
		$userModel = $this->createStub(UserModel::class);
		$paymentModel->method('read')->willReturn($payment);

		$service = new PaymentService(
			$session,
			$paymentModel,
			$userModel
		);

		self::assertSame($payment, $service->read(19));
	}

	#endregion test read()

	#region test readAll()

	public function testReadAllThrowsWhenNotAuthenticated() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$paymentModel = $this->createStub(PaymentModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new PaymentService(
			$session,
			$paymentModel,
			$userModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(PaymentService::ERROR_UNAUTHENTICATED_READ);
		$service->readAll([]);
	}

	public function testReadAllThrowsWhenNotAuthorized() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$paymentModel = $this->createStub(PaymentModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new PaymentService(
			$session,
			$paymentModel,
			$userModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(PaymentService::ERROR_UNAUTHORIZED_READ);
		$service->readAll([]);
	}

	public function testReadAllThrowsWhenUserMayReadOwnPaymentButUserFilterNotSet() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_id(1)
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::LIST_OWN_PAYMENTS])
				)
		);

		$paymentModel = $this->createStub(PaymentModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new PaymentService(
			$session,
			$paymentModel,
			$userModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(PaymentService::ERROR_UNAUTHORIZED_READ);
		$service->readAll([]);
	}

	public function testReadAllThrowsWhenUserMayReadOwnPaymentButUserFilterNotUserId() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_id(1)
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::LIST_OWN_PAYMENTS])
				)
		);

		$paymentModel = $this->createStub(PaymentModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new PaymentService(
			$session,
			$paymentModel,
			$userModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(PaymentService::ERROR_UNAUTHORIZED_READ);
		$service->readAll(['user_id' => '2']);
	}

	public function testReadAllThrowsWhenUserFilterIsNotInt() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::LIST_PAYMENTS])
				)
		);

		$paymentModel = $this->createStub(PaymentModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new PaymentService(
			$session,
			$paymentModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(PaymentService::ERROR_USER_FILTER_MUST_BE_INT);
		$service->readAll(['user_id' => 'apple']);
	}

	public function testReadAllThrowsWhenAfterFilterIsNotDate() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::LIST_PAYMENTS])
				)
		);

		$paymentModel = $this->createStub(PaymentModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new PaymentService(
			$session,
			$paymentModel,
			$userModel
		);

		self::expectException(Exception::class);
		// we rely on PHP's exception message which can change without notice so no assertion
		$service->readAll(['after' => 'apple']);
	}

	public function testReadAllThrowsWhenBeforeFilterIsNotDate() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::LIST_PAYMENTS])
				)
		);

		$paymentModel = $this->createStub(PaymentModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new PaymentService(
			$session,
			$paymentModel,
			$userModel
		);

		self::expectException(Exception::class);
		// we rely on PHP's exception message which can change without notice so no assertion
		$service->readAll(['before' => 'apple']);
	}

	public function testReadAllSuccessAsAdmin() {
		$user_id = 2;
		$after = '2025-03-01';
		$before = '2026-03-19';
		$payments = [new Payment()];

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_id(1)
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::LIST_PAYMENTS])
				)
		);

		$paymentModel = $this->createMock(PaymentModel::class);
		$paymentModel->expects($this->once())->method('search')->with(
			$this->callback(
				fn(PaymentQuery $query) =>
					$query->user_id() === $user_id
					&& $query->on_or_after()->format('Y-m-d') === $after
					&& $query->on_or_before()->format('Y-m-d') === $before
			)
		)->willReturn($payments);

		$userModel = $this->createStub(UserModel::class);

		$service = new PaymentService(
			$session,
			$paymentModel,
			$userModel
		);

		self::assertSame(
			$payments,
			$service->readAll([
				'user_id' => $user_id,
				'after' => $after,
				'before' => $before
			])
		);
	}

	public function testReadAllSuccessForOwnPayments() {
		$user_id = 2;
		$after = '2025-03-01';
		$before = '2026-03-19';
		$payments = [new Payment()];

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_id($user_id)
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::LIST_OWN_PAYMENTS])
				)
		);

		$paymentModel = $this->createMock(PaymentModel::class);
		$paymentModel->expects($this->once())->method('search')->with(
			$this->callback(
				fn(PaymentQuery $query) =>
					$query->user_id() === $user_id
					&& $query->on_or_after()->format('Y-m-d') === $after
					&& $query->on_or_before()->format('Y-m-d') === $before
			)
		)->willReturn($payments);

		$userModel = $this->createStub(UserModel::class);

		$service = new PaymentService(
			$session,
			$paymentModel,
			$userModel
		);

		self::assertSame(
			$payments,
			$service->readAll([
				'user_id' => $user_id,
				'after' => $after,
				'before' => $before
			])
		);
	}

	#endregion test readAll()

	#region test update()

	public function testUpdateThrowsWhenNotAuthenticated() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$paymentModel = $this->createStub(PaymentModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new PaymentService(
			$session,
			$paymentModel,
			$userModel
 		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(PaymentService::ERROR_UNAUTHENTICATED_UPDATE);
		$service->update(1, 'not a file path');
	}

	public function testUpdateThrowsWhenNotAuthorized() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$paymentModel = $this->createStub(PaymentModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new PaymentService(
			$session,
			$paymentModel,
			$userModel
 		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(PaymentService::ERROR_UNAUTHORIZED_MODIFY);
		$service->update(1, 'not a file path');
	}

	public function testUpdateThrowsWhenFileIsNotReadable() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_PAYMENT])
				)
		);

		$paymentModel = $this->createStub(PaymentModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new PaymentService(
			$session,
			$paymentModel,
			$userModel
 		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(PaymentService::ERROR_INVALID_PAYMENT_DATA);
		// PHP warning is intentionally suppressed in next line for testing
		@$service->update(1, 'file_does_not_exist.json');
	}

	public function testUpdateThrowsWhenDataIsNotArray() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_PAYMENT])
				)
		);

		$paymentModel = $this->createStub(PaymentModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new PaymentService(
			$session,
			$paymentModel,
			$userModel
 		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(PaymentService::ERROR_INVALID_PAYMENT_DATA);
		$service->update(1, realpath(__DIR__ . '/PaymentServiceTestData/DataIsNotArray.json'));
	}

	public function testUpdateThrowsWhenUserNotSpecified() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_PAYMENT])
				)
		);

		$paymentModel = $this->createStub(PaymentModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new PaymentService(
			$session,
			$paymentModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(PaymentService::ERROR_USER_ID_IS_REQUIRED);
		$service->update(1, realpath(__DIR__ . '/PaymentServiceTestData/UserNotSpecified.json'));
	}

	public function testUpdateThrowsWhenUserIdIsNotInt() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_PAYMENT])
				)
		);

		$paymentModel = $this->createStub(PaymentModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new PaymentService(
			$session,
			$paymentModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(PaymentService::ERROR_USER_ID_IS_REQUIRED);
		$service->update(1, realpath(__DIR__ . '/PaymentServiceTestData/UserIdIsNotInt.json'));
	}

	public function testUpdateThrowsWhenUserIsInvalid() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_PAYMENT])
				)
		);

		$paymentModel = $this->createStub(PaymentModel::class);

		$userModel = $this->createStub(UserModel::class);
		$userModel->method('read')->willReturn(null);

		$service = new PaymentService(
			$session,
			$paymentModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(PaymentService::ERROR_INVALID_USER_ID);
		$service->update(1, realpath(__DIR__ . '/PaymentServiceTestData/ValidPayment.json'));
	}

	public function testUpdateThrowsWhenAmountIsNotSpecified() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_PAYMENT])
				)
		);

		$paymentModel = $this->createStub(PaymentModel::class);

		$userModel = $this->createStub(UserModel::class);
		$userModel->method('read')->willReturn(new User());

		$service = new PaymentService(
			$session,
			$paymentModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(PaymentService::ERROR_INVALID_AMOUNT);
		$service->update(1, realpath(__DIR__ . '/PaymentServiceTestData/AmountIsNotSpecified.json'));
	}

	public function testUpdateThrowsWhenAmountIsNotDecimal() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_PAYMENT])
				)
		);

		$paymentModel = $this->createStub(PaymentModel::class);

		$userModel = $this->createStub(UserModel::class);
		$userModel->method('read')->willReturn(new User());

		$service = new PaymentService(
			$session,
			$paymentModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(PaymentService::ERROR_INVALID_AMOUNT);
		$service->update(1, realpath(__DIR__ . '/PaymentServiceTestData/AmountIsNotDecimal.json'));
	}

	public function testUpdateThrowsWhenTimeIsNotSpecified() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_PAYMENT])
				)
		);

		$paymentModel = $this->createStub(PaymentModel::class);

		$userModel = $this->createStub(UserModel::class);
		$userModel->method('read')->willReturn(new User());

		$service = new PaymentService(
			$session,
			$paymentModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(PaymentService::ERROR_TIME_IS_REQUIRED);
		$service->update(1, realpath(__DIR__ . '/PaymentServiceTestData/TimeIsNotSpecified.json'));
	}

	public function testUpdateThrowsWhenTimeIsInvalid() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_PAYMENT])
				)
		);

		$paymentModel = $this->createStub(PaymentModel::class);

		$userModel = $this->createStub(UserModel::class);
		$userModel->method('read')->willReturn(new User());

		$service = new PaymentService(
			$session,
			$paymentModel,
			$userModel
		);

		self::expectException(Exception::class);
		// we rely on PHP's exception message which can change without notice so no assertion
		$service->update(1, realpath(__DIR__ . '/PaymentServiceTestData/TimeIsInvalid.json'));
	}

	public function testUpdateThrowsWhenPaymentNotFound() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_PAYMENT])
				)
		);

		$paymentModel = $this->createStub(PaymentModel::class);
		$paymentModel->method('update')->willReturn(null);

		$userModel = $this->createStub(UserModel::class);
		$userModel->method('read')->willReturn((new User())->set_id(501));

		$service = new PaymentService(
			$session,
			$paymentModel,
			$userModel
		);

		self::expectException(NotFoundException::class);
		self::expectExceptionMessage(PaymentService::ERROR_PAYMENT_NOT_FOUND);
		$service->update(1, realpath(__DIR__ . '/PaymentServiceTestData/ValidPayment.json'));
	}

	public function testUpdateSuccess() {
		$id = 1234;

		// values matching the input file
		$user_id = 2;
		$time = "2026-03-25 10:10:23";
		$amount = "23.56";

		$user = (new User())->set_id($user_id);

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_PAYMENT])
				)
		);

		$paymentModel = $this->createMock(PaymentModel::class);
		$paymentModel->expects($this->once())->method('update')->with(
			$this->callback(
				fn(Payment $payment) =>
					$payment->id() === $id
					&& $payment->user() === $user
					&& $payment->amount() === $amount
					&& $payment->time() === $time
			)
		)
		->willReturnArgument(0);

		$userModel = $this->createStub(UserModel::class);
		$userModel->method('read')->willReturn($user);

		$service = new PaymentService(
			$session,
			$paymentModel,
			$userModel
		);

		$payment = $service->update($id, realpath(__DIR__ . '/PaymentServiceTestData/ValidPayment.json'));

		self::assertInstanceOf(Payment::class, $payment);
		self::assertSame($id, $payment->id());
		self::assertSame($user, $payment->user());
		self::assertSame($amount, $payment->amount());
		self::assertSame($time, $payment->time());
	}

	#endregion test update()
}
