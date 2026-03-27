<?php

declare(strict_types=1);

namespace Portalbox\Service;

use DateTimeImmutable;
use InvalidArgumentException;
use Portalbox\Enumeration\Permission;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Exception\NotFoundException;
use Portalbox\Model\PaymentModel;
use Portalbox\Model\UserModel;
use Portalbox\Query\PaymentQuery;
use Portalbox\Session;
use Portalbox\Type\Payment;

/**
 * Manage Payments
 */
class PaymentService {
	public const ERROR_UNAUTHENTICATED_CREATE = 'You must be authenticated to create payments';
	public const ERROR_UNAUTHORIZED_CREATE = 'You are not authorized to create payments';
	public const ERROR_INVALID_PAYMENT_DATA = 'We can not create a payment from the provided data';
	public const ERROR_USER_ID_IS_REQUIRED = '\'user_id\' is a required field';
	public const ERROR_INVALID_USER_ID = '\'user_id\' must correspond to a valid user';
	public const ERROR_INVALID_AMOUNT = '\'amount\' is a required field and must be a positive number with up to two decimal places';
	public const ERROR_TIME_IS_REQUIRED = '\'time\' is a required field';

	public const ERROR_UNAUTHENTICATED_READ = 'You must be authenticated to read payments';
	public const ERROR_UNAUTHORIZED_READ = 'You are not authorized to read the specified payment(s)';
	public const ERROR_PAYMENT_NOT_FOUND = 'We have no record of that payment';

	public const ERROR_USER_FILTER_MUST_BE_INT = 'The value of user_id must be an integer';

	public const ERROR_UNAUTHENTICATED_UPDATE = 'You must be authenticated to modify payments';
	public const ERROR_UNAUTHORIZED_MODIFY = 'You are not permitted to modify payments';

	protected Session $session;
	protected PaymentModel $paymentModel;
	protected UserModel $userModel;

	public function __construct(
		Session $session,
		PaymentModel $paymentModel,
		UserModel $userModel
	) {
		$this->session = $session;
		$this->paymentModel = $paymentModel;
		$this->userModel = $userModel;
	}

	/**
	 * Deserialize a Payment object from a dictionary
	 *
	 * @param array data - a dictionary representing a Payment
	 * @return Payment - an object based on the data specified
	 * @throws InvalidArgumentException if a require field is not specified
	 * @throws Exception if time is not a valid date time string
	 */
	public function deserialize(array $data): Payment {
		$user_id = filter_var($data['user_id'] ?? '', FILTER_VALIDATE_INT);
		if ($user_id === false) {
			throw new InvalidArgumentException(self::ERROR_USER_ID_IS_REQUIRED);
		}
		$user = $this->userModel->read($user_id);
		if ($user === null) {
			throw new InvalidArgumentException(self::ERROR_INVALID_USER_ID);
		}

		$amount = filter_var(
			$data['amount'] ?? '',
			FILTER_VALIDATE_FLOAT,
			['options' => ['min_range' => 0.0]]
		);
		if ($amount === false) {
			throw new InvalidArgumentException(self::ERROR_INVALID_AMOUNT);
		}

		if (!array_key_exists('time', $data)) {
			throw new InvalidArgumentException(self::ERROR_TIME_IS_REQUIRED);
		}
		$time = new DateTimeImmutable($data['time']);

		return (new Payment())
			->set_user($user)
			->set_amount((string)$amount)
			->set_time($data['time']);
	}

	/**
	 * Create payment from the specified data stream
	 *
	 * @param string $filePath  the path to a file from which to read json data
	 * @return Payment  The payment which was added
	 * @throws AuthenticationException  if no user is authenticated
	 * @throws AuthorizationException  if the authenticated user may not create
	 *      payments
	 * @throws InvalidArgumentException  if the file can not be read or does not
	 *      contain JSON encoded data
	 */
	public function create(string $filePath): Payment {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException(self::ERROR_UNAUTHENTICATED_CREATE);
		}

		if (!$authenticatedUser->role()->has_permission(Permission::CREATE_PAYMENT)) {
			throw new AuthorizationException(self::ERROR_UNAUTHORIZED_CREATE);
		}

		$data = file_get_contents($filePath);
		if ($data === false) {
			throw new InvalidArgumentException(self::ERROR_INVALID_PAYMENT_DATA);
		}

		$payment = json_decode($data, TRUE);
		if (!is_array($payment)) {
			throw new InvalidArgumentException(self::ERROR_INVALID_PAYMENT_DATA);
		}

		return $this->paymentModel->create($this->deserialize($payment));
	}

	/**
	 * Read a payment by id
	 *
	 * @param int $id  the unique id of the payment to read
	 * @return Payment  the payment
	 * @throws AuthenticationException  if no user is authenticated
	 * @throws AuthorizationException  if the authenticated user may not read
	 *      the payment
	 * @throws NotFoundException  if the payment is not found
	 */
	public function read(int $id): Payment {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException(self::ERROR_UNAUTHENTICATED_READ);
		}

		if (!$authenticatedUser->role()->has_permission(Permission::READ_PAYMENT)) {
			throw new AuthorizationException(self::ERROR_UNAUTHORIZED_READ);
		}

		$payment = $this->paymentModel->read($id);
		if ($payment === null) {
			throw new NotFoundException(self::ERROR_PAYMENT_NOT_FOUND);
		}

		return $payment;
	}

	/**
	 * Read all payments
	 *
	 * @param array<string, string>  filters that all payments in the result set
	 *      must match
	 * @return Payment[]  the payments
	 * @throws AuthenticationException  if no user is authenticated
	 * @throws AuthorizationException  if the authenticated user may not read
	 *      the payments
	 */
	public function readAll(array $filters): array {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException(self::ERROR_UNAUTHENTICATED_READ);
		}

		$role = $authenticatedUser->role();
		if (!$role->has_permission(Permission::LIST_PAYMENTS)) {
			if ($role->has_permission(Permission::LIST_OWN_PAYMENTS)) {
				$userId = $filters['user_id'] ?? '';
				if ($authenticatedUser->id() != $userId) {
					throw new AuthorizationException(self::ERROR_UNAUTHORIZED_READ);
				}
			} else {
				throw new AuthorizationException(self::ERROR_UNAUTHORIZED_READ);
			}
		}

		$query = new PaymentQuery();

		if (isset($filters['user_id']) && !empty($filters['user_id'])) {
			$user_id = filter_var($filters['user_id'], FILTER_VALIDATE_INT);
			if ($user_id === false) {
				throw new InvalidArgumentException(self::ERROR_USER_FILTER_MUST_BE_INT);
			}

			$query->set_user_id($user_id);
		}

		if(isset($filters['after']) && !empty($filters['after'])) {
			$after = new DateTimeImmutable($filters['after']);
			$query->set_on_or_after($after);
		}

		if(isset($filters['before']) && !empty($filters['before'])) {
			$before = new DateTimeImmutable($filters['before']);
			$query->set_on_or_before($before);
		}

		return $this->paymentModel->search($query);
	}

	/**
	 * Modify payment using data read from the specified data stream
	 *
	 * @param int $id  the unique id of the payment to modify
	 * @param string $filePath  the path to a file from which to read json data
	 * @return Payment  the payment as modified
	 * @throws AuthenticationException  if no user is authenticated
	 * @throws AuthorizationException  if the authenticated user may not update
	 *      payment
	 * @throws InvalidArgumentException  if the file can not be read or does not
	 *      contain JSON encoded data
	 */
	public function update(int $id, string $filePath): Payment {
		$authenticatedUser = $this->session->get_authenticated_user();
		if ($authenticatedUser === null) {
			throw new AuthenticationException(self::ERROR_UNAUTHENTICATED_UPDATE);
		}

		if (!$authenticatedUser->role()->has_permission(Permission::MODIFY_PAYMENT)) {
			throw new AuthorizationException(self::ERROR_UNAUTHORIZED_MODIFY);
		}

		$data = file_get_contents($filePath);
		if ($data === false) {
			throw new InvalidArgumentException(self::ERROR_INVALID_PAYMENT_DATA);
		}

		$payment = json_decode($data, TRUE);
		if (!is_array($payment)) {
			throw new InvalidArgumentException(self::ERROR_INVALID_PAYMENT_DATA);
		}

		$payment = $this->paymentModel->update(
			$this->deserialize($payment)->set_id($id)
		);
		if ($payment === null) {
			throw new NotFoundException(self::ERROR_PAYMENT_NOT_FOUND);
		}

		return $payment;
	}
}
