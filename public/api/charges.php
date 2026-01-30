<?php

require '../../src/autoload.php';

use Portalbox\Config;
use Portalbox\ResponseHandler;
use Portalbox\Enumeration\Permission;
use Portalbox\Exception\NotFoundException;
use Portalbox\Model\ChargeModel;
use Portalbox\Query\ChargeQuery;
use Portalbox\Session;
use Portalbox\Transform\ChargeTransformer;

$session = new Session();

try {
	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET':		// List/Read
			if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
				$session->require_authorization(Permission::READ_CHARGE);

				$model = new ChargeModel(Config::config());
				$charge = $model->read($_GET['id']);
				if(!$charge) {
					throw new NotFoundException('We have no record of that charge');
				}

				$transformer = new ChargeTransformer();
				ResponseHandler::render($charge, $transformer);
			} else { // List
				$user_id = NULL;

				// check authorization
				if($session->check_authorization(Permission::LIST_OWN_CHARGES)) {
					if(!$session->check_authorization(Permission::LIST_CHARGES)) {
						$user_id = $session->get_authenticated_user()->id();
					}
				} else {
					$session->require_authorization(Permission::LIST_CHARGES);
				}

				$model = new ChargeModel(Config::config());
				$query = new ChargeQuery();
				
				if(isset($_GET['equipment_id']) && !empty($_GET['equipment_id'])) {
					$query->set_equipment_id($_GET['equipment_id']);
				}
				
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

				$charges = $model->search($query);
				$transformer = new ChargeTransformer();
				ResponseHandler::render($charges, $transformer);
			}
			break;
		case 'POST':	// Update
			// validate that we have an oid
			if(!isset($_GET['id']) || empty($_GET['id'])) {
				throw new InvalidArgumentException('You must specify the charge to modify via the id param');
			}

			// check authorization
			$session->require_authorization(Permission::MODIFY_CHARGE);

			$data = json_decode(file_get_contents('php://input'), TRUE);
			if($data === null) {
				throw new InvalidArgumentException(json_last_error_msg());
			}

			$transformer = new ChargeTransformer();
			$charge = $transformer->deserialize($data);
			$charge->set_id($_GET['id']);
			$model = new ChargeModel(Config::config());
			$charge = $model->update($charge);
			ResponseHandler::render($charge, $transformer);
			break;
		case 'PUT':		// Create
			// check authorization
			$session->require_authorization(Permission::CREATE_CHARGE);

			$data = json_decode(file_get_contents('php://input'), TRUE);
			if($data === null) {
				throw new InvalidArgumentException(json_last_error_msg());
			}

			$transformer = new ChargeTransformer();
			$charge = $transformer->deserialize($data);
			$model = new ChargeModel(Config::config());
			$charge = $model->create($charge);
			ResponseHandler::render($charge, $transformer);
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
