<?php

require '../../src/autoload.php';

use Portalbox\Config;
use Portalbox\ResponseHandler;
use Portalbox\Session;

use Portalbox\Entity\Permission;

use Portalbox\Model\LoggedEventModel;

use Portalbox\Query\LoggedEventQuery;

use Portalbox\Transform\LoggedEventTransformer;

// switch on the request method
switch($_SERVER['REQUEST_METHOD']) {
	case 'GET':		// List
		// check authorization
		Session::require_authorization(Permission::LIST_LOGS);

		try {
			$model = new LoggedEventModel(Config::config());
			$query = new LoggedEventQuery();
			if(isset($_GET['equipment_id']) && !empty($_GET['equipment_id'])) {
				$query->set_equipment_id($_GET['equipment_id']);
			}
			if(isset($_GET['equipment_type_id']) && !empty($_GET['equipment_type_id'])) {
				$query->set_equipment_type_id($_GET['equipment_type_id']);
			}
			if(isset($_GET['location_id']) && !empty($_GET['location_id'])) {
				$query->set_location_id($_GET['location_id']);
			}
			if(isset($_GET['type_id']) && !empty($_GET['type_id'])) {
				$query->set_type_id($_GET['type_id']);
			}
			if(isset($_GET['after']) && !empty($_GET['after'])) {
				$query->set_on_or_after($_GET['after']);
			}
			if(isset($_GET['before']) && !empty($_GET['before'])) {
				$query->set_on_or_before($_GET['before']);
			}

			$log = $model->search($query);
			$transformer = new LoggedEventTransformer();
			ResponseHandler::render($log, $transformer);
		} catch(Exception $e) {
			http_response_code(500);
			die('We experienced issues communicating with the database');
		}
		break;
	case 'POST':	// Update
		// intentional fall through, users should not modify log entries
	case 'PUT':		// Create
		// intentional fall through, users should not create log entries
	case 'DELETE':	// Delete
		// intentional fall through, deletion not allowed
	default:
		http_response_code(405);
		die('We were unable to understand your request.');
}
