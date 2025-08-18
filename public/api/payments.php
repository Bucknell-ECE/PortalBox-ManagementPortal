<?php

require '../../src/autoload.php';

use Portalbox\Config;
use Portalbox\ResponseHandler;
use Portalbox\Entity\Permission;
use Portalbox\Exception\NotFoundException;
use Portalbox\Model\PaymentModel;
use Portalbox\Query\PaymentQuery;
use Portalbox\Session\Session;
use Portalbox\Transform\PaymentTransformer;

$session = new Session();

try {
	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET':		// List/Read
			if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
				// check authorization
				$session->require_authorization(Permission::READ_PAYMENT);

				$model = new PaymentModel(Config::config());
				$payment = $model->read($_GET['id']);
				if(!$payment) {
					throw new NotFoundException('We have no record of that payment');
				}

				$transformer = new PaymentTransformer();
				ResponseHandler::render($payment, $transformer);
			} else { // List
				$user_id = NULL;

				// check authorization
				if($session->check_authorization(Permission::LIST_OWN_PAYMENTS)) {
					if(!$session->check_authorization(Permission::LIST_PAYMENTS)) {
						$user_id = $session->get_authenticated_user()->id();
					}
				} else {
					$session->require_authorization(Permission::LIST_PAYMENTS);
				}

				$model = new PaymentModel(Config::config());
				$query = new PaymentQuery();
				if(NULL !== $user_id) {
					$query->set_user_id($user_id);
				} else if(isset($_GET['user_id']) && !empty($_GET['user_id'])) {
					$query->set_user_id($_GET['user_id']);
				}
				if(isset($_GET['after']) && !empty($_GET['after'])) {
					$query->set_on_or_after($_GET['after']);
				}
				if(isset($_GET['before']) && !empty($_GET['before'])) {
					$query->set_on_or_before($_GET['before']);
				}

				$payments = $model->search($query);
				$transformer = new PaymentTransformer();
				ResponseHandler::render($payments, $transformer);
			}
			break;
		case 'POST':	// Update
			// validate that we have an oid
			if(!isset($_GET['id']) || empty($_GET['id'])) {
				throw new InvalidArgumentException('You must specify the payment to modify via the id param');
			}

			// check authorization
			$session->require_authorization(Permission::MODIFY_PAYMENT);

			$data = json_decode(file_get_contents('php://input'), TRUE);
			if($data === null) {
				throw new InvalidArgumentException(json_last_error_msg());
			}

			$transformer = new PaymentTransformer();
			$payment = $transformer->deserialize($data);
			$payment->set_id($_GET['id']);
			$model = new PaymentModel(Config::config());
			$payment = $model->update($payment);
			ResponseHandler::render($payment, $transformer);
			break;
		case 'PUT':		// Create
			// check authorization
			$session->require_authorization(Permission::CREATE_PAYMENT);

			$data = json_decode(file_get_contents('php://input'), TRUE);
			if($data === null) {
				throw new InvalidArgumentException(json_last_error_msg());
			}

			$transformer = new PaymentTransformer();
			$payment = $transformer->deserialize($data);
			$model = new PaymentModel(Config::config());
			$payment = $model->create($payment);
			ResponseHandler::render($payment, $transformer);
			break;
		case 'DELETE':	// Delete
			// intentional fall through, deletion not allowed, but maybe it should be?
		default:
			http_response_code(405);
			die('We were unable to understand your request.');
	}
} catch(Throwable $t) {
	ResponseHandler::setResponseCode($t);
	$message = $t->getMessage();
	if (empty($message)) {
		$message = ResponseHandler::GENERIC_ERROR_MESSAGE;
	}
	die($message);
}
