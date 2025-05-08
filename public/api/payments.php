<?php

require '../../src/autoload.php';

use Portalbox\Config;
use Portalbox\ResponseHandler;
use Portalbox\Session;

use Portalbox\Entity\Permission;

use Portalbox\Model\PaymentModel;

use Portalbox\Query\PaymentQuery;

use Portalbox\Transform\PaymentTransformer;

// switch on the request method
switch($_SERVER['REQUEST_METHOD']) {
	case 'GET':		// List/Read
		if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
			// check authorization
			Session::require_authorization(Permission::READ_PAYMENT);

			try {
				$model = new PaymentModel(Config::config());
				$payment = $model->read($_GET['id']);
				if($payment) {
					$transformer = new PaymentTransformer();
					ResponseHandler::render($payment, $transformer);
				} else {
					http_response_code(404);
					die('We have no record of that payment');
				}
			} catch(Exception $e) {
				http_response_code(500);
				die('We experienced issues communicating with the database');
			}
		} else { // List
			$user_id = NULL;

			// check authorization
			if(Session::check_authorization(Permission::LIST_OWN_PAYMENTS)) {
				if(!Session::check_authorization(Permission::LIST_PAYMENTS)) {
					$user_id = Session::get_authenticated_user()->id();
				}
			} else {
				Session::require_authorization(Permission::LIST_PAYMENTS);
			}

			try {
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
			} catch(Exception $e) {
				http_response_code(500);
				die('We experienced issues communicating with the database');
			}
		}
		break;
	case 'POST':	// Update
		// validate that we have an oid
		if(!isset($_GET['id']) || empty($_GET['id'])) {
			http_response_code(400);
			die('You must specify the payment to modify via the id param');
		}

		// check authorization
		Session::require_authorization(Permission::MODIFY_PAYMENT);

		$data = json_decode(file_get_contents('php://input'), TRUE);
		if(NULL !== $data) {
			try {
				$transformer = new PaymentTransformer();
				$payment = $transformer->deserialize($data);
				$payment->set_id($_GET['id']);
				$model = new PaymentModel(Config::config());
				$payment = $model->update($payment);
				ResponseHandler::render($payment, $transformer);
			} catch(InvalidArgumentException $iae) {
				http_response_code(400);
				die($iae->getMessage());
			} catch(Exception $e) {
				http_response_code(500);
				die('We experienced issues communicating with the database');
			}
		} else {
			http_response_code(400);
			die(json_last_error_msg());
		}
		break;
	case 'PUT':		// Create
		// check authorization
		Session::require_authorization(Permission::CREATE_PAYMENT);

		$data = json_decode(file_get_contents('php://input'), TRUE);
		if(NULL !== $data) {
			try {
				$transformer = new PaymentTransformer();
				$payment = $transformer->deserialize($data);
				$model = new PaymentModel(Config::config());
				$payment = $model->create($payment);
				ResponseHandler::render($payment, $transformer);
			} catch(InvalidArgumentException $iae) {
				http_response_code(400);
				die($iae->getMessage());
			} catch(Exception $e) {
				http_response_code(500);
				die('We experienced issues communicating with the database');
			}
		} else {
			http_response_code(400);
			die(json_last_error_msg());
		}
		break;
	case 'DELETE':	// Delete
		// intentional fall through, deletion not allowed, but maybe it should be?
	default:
		http_response_code(405);
		die('We were unable to understand your request.');
}
