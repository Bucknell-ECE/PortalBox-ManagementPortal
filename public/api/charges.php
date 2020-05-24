<?php

require '../../src/autoload.php';

use Portalbox\Config;
use Portalbox\ResponseHandler;
use Portalbox\Session;

use Portalbox\Entity\Permission;

use Portalbox\Model\ChargeModel;

use Portalbox\Query\ChargeQuery;

use Portalbox\Transform\ChargeTransformer;

// switch on the request method
switch($_SERVER['REQUEST_METHOD']) {
	case 'GET':		// List/Read
		if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
			Session::require_authorization(Permission::READ_CHARGE);

			try {
				$model = new ChargeModel(Config::config());
				$charge = $model->read($_GET['id']);
				if($charge) {
					$transformer = new ChargeTransformer();
					ResponseHandler::render($charge, $transformer);
				} else {
					header('HTTP/1.0 404 Not Found');
					die('We have no record of that charge');
				}
			} catch(Exception $e) {
				header('HTTP/1.0 500 Internal Server Error');
				die('We experienced issues communicating with the database');
			}
		} else { // List
			$user_id = NULL;

			// check authorization
			if(!Session::check_authorization(Permission::LIST_OWN_CHARGES)) {
				Session::require_authorization(Permission::LIST_CHARGES);
				$user_id = Session::get_authenticated_user()->id();
			}

			try {
				$model = new ChargeModel(Config::config());
				$query = new ChargeQuery();
				if(isset($_GET['equipment_id']) && !empty($_GET['equipment_id'])) {
					$query->set_equipment_id($_GET['equipment_id']);
				}
				if($user_id) {
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

				$charges = $model->search($query);
				$transformer = new ChargeTransformer();
				ResponseHandler::render($charges, $transformer);
			} catch(Exception $e) {
				header('HTTP/1.0 500 Internal Server Error');
				die('We experienced issues communicating with the database');
			}
		}
		break;
	case 'POST':	// Update
		// validate that we have an oid
		if(!isset($_GET['id']) || empty($_GET['id'])) {
			header('HTTP/1.0 400 Bad Request');
			die('You must specify the charge to modify via the id param');
		}

		// check authorization
		Session::require_authorization(Permission::MODIFY_CHARGE);

		$data = json_decode(file_get_contents('php://input'), TRUE);
		if(NULL !== $data) {
			try {
				$transformer = new ChargeTransformer();
				$charge = $transformer->deserialize($data);
				$charge->set_id($_GET['id']);
				$model = new ChargeModel(Config::config());
				$charge = $model->update($charge);
				ResponseHandler::render($charge, $transformer);
			} catch(Exception $e) { // we could have a validation error...
				header('HTTP/1.0 500 Internal Server Error');
				die('We experienced issues communicating with the database');
			}
		} else {
			header('HTTP/1.0 400 Bad Request');
			die(json_last_error_msg());
		}
		break;
	case 'PUT':		// Create
		// intentional fall through, creation not allowed
	case 'DELETE':	// Delete
		// intentional fall through, deletion not allowed, but maybe it should be?
	default:
		header('HTTP/1.0 405 Method Not Allowed');
		die('We were unable to understand your request.');
}
