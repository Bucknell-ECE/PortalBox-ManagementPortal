<?php

require '../../src/autoload.php';

use Portalbox\Config;
use Portalbox\ResponseHandler;
use Portalbox\Session;

use Portalbox\Entity\Permission;

use Portalbox\Model\EquipmentModel;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Model\LocationModel;

use Portalbox\Query\EquipmentQuery;

use Portalbox\Transform\EquipmentTransformer;

// switch on the request method
switch($_SERVER['REQUEST_METHOD']) {
	case 'GET':		// List/Read
		if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
			// check authorization
			Session::require_authorization(Permission::READ_EQUIPMENT);

			try {
				$model = new EquipmentModel(Config::config());
				$equipment = $model->read($_GET['id']);
				if($equipment) {
					$transformer = new EquipmentTransformer();
					ResponseHandler::render($equipment, $transformer);
				} else {
					header('HTTP/1.0 404 Not Found');
					die('We have no record of that equipment');
				}
			} catch(Exception $e) {
				header('HTTP/1.0 500 Internal Server Error');
				die('We experienced issues communicating with the database');
			}
		} else { // List
			// no authorization check as unauthenticated users may use

			try {
				$model = new EquipmentModel(Config::config());
				$query = new EquipmentQuery();
				if(isset($_GET['location_id']) && !empty($_GET['location_id'])) {
					$query->set_location_id($_GET['location_id']);
				} else if(isset($_GET['location']) && !empty($_GET['location'])) {
					$query->set_location($_GET['location']);
				}
				if(isset($_GET['type']) && !empty($_GET['type'])) {
					$query->set_type($_GET['type']);
				}
				if(isset($_GET['include_out_of_service']) && !empty($_GET['include_out_of_service'])) {
					$query->set_include_out_of_service(true);
				}

				$equipment = $model->search($query);
				$transformer = new EquipmentTransformer();
				ResponseHandler::render($equipment, $transformer);
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
			die('You must specify the location to modify via the id param');
		}

		// check authorization
		Session::require_authorization(Permission::MODIFY_EQUIPMENT);

		$data = json_decode(file_get_contents('php://input'), TRUE);
		if(NULL !== $data) {
			try {
				$transformer = new EquipmentTransformer();
				$equipment = $transformer->deserialize($data);
				$equipment->set_id($_GET['id']);
				$model = new EquipmentModel(Config::config());
				$equipment = $model->update($equipment);
				ResponseHandler::render($equipment, $transformer);
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
		// check authorization
		Session::require_authorization(Permission::CREATE_EQUIPMENT);

		$data = json_decode(file_get_contents('php://input'), TRUE);
		if(NULL !== $data) {
			try {
				$transformer = new EquipmentTransformer();
				$equipment = $transformer->deserialize($data);
				$model = new EquipmentModel(Config::config());
				$equipment = $model->create($equipment);
				ResponseHandler::render($equipment, $transformer);
			} catch(Exception $e) { // we could have a validation error...
				header('HTTP/1.0 500 Internal Server Error');
				die('We experienced issues communicating with the database');
			}
		} else {
			header('HTTP/1.0 400 Bad Request');
			die(json_last_error_msg());
		}
		break;
	case 'DELETE':	// Delete
		// intentional fall through, deletion not allowed
	default:
		header('HTTP/1.0 405 Method Not Allowed');
		die('We were unable to understand your request.');
}
