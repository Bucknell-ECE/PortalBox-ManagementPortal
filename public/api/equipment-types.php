<?php

require '../../src/autoload.php';

use Portalbox\Config;
use Portalbox\ResponseHandler;
use Portalbox\Session;

use Portalbox\Entity\Permission;

use Portalbox\Model\EquipmentTypeModel;

//use Portalbox\Query\EquipmentTypeQuery;

use Portalbox\Transform\EquipmentTypeTransformer;

// switch on the request method
switch($_SERVER['REQUEST_METHOD']) {
	case 'GET':		// List/Read
		if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
			// check authorization
			Session::require_authorization(Permission::READ_EQUIPMENT_TYPE);

			try {
				$model = new EquipmentTypeModel(Config::config());
				$equipment_type = $model->read($_GET['id']);
				if($equipment_type) {
					$transformer = new EquipmentTypeTransformer();
					ResponseHandler::render($equipment_type, $transformer);
				} else {
					header('HTTP/1.0 404 Not Found');
					die('We have no record of that equipment type');
				}
			} catch(Exception $e) {
				header('HTTP/1.0 500 Internal Server Error');
				die('We experienced issues communicating with the database');
			}
		} else { // List
			// check authorization
			Session::require_authorization(Permission::LIST_EQUIPMENT_TYPES);

			try {
				$model = new EquipmentTypeModel(Config::config());
				$equipment_types = $model->search();
				$transformer = new EquipmentTypeTransformer();
				ResponseHandler::render($equipment_types, $transformer);
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
			die('You must specify the equipment type to modify via the id param');
		}

		// check authorization
		Session::require_authorization(Permission::MODIFY_EQUIPMENT_TYPE);

		$data = json_decode(file_get_contents('php://input'), TRUE);
		if(NULL !== $data) {
			try {
				$transformer = new EquipmentTypeTransformer();
				$equipment_type = $transformer->deserialize($data);
				$equipment_type->set_id($_GET['id']);
				$model = new EquipmentTypeModel(Config::config());
				$equipment_type = $model->update($equipment_type);
				ResponseHandler::render($equipment_type, $transformer);
			} catch(Exception $e) { // we could have a validation error...
				header('HTTP/1.0 500 Internal Server Error');
				die('We experienced issues communicating with the database');
			}
		} else {
			header('HTTP/1.0 400 Bad Request');
			die(json_last_error_msg());
		}
		break;
	case 'PUT':	// Create
		// check authorization
		Session::require_authorization(Permission::CREATE_EQUIPMENT_TYPE);

		$data = json_decode(file_get_contents('php://input'), TRUE);
		if(NULL !== $data) {
			try {
				$transformer = new EquipmentTypeTransformer();
				$equipment_type = $transformer->deserialize($data);
				$model = new EquipmentTypeModel(Config::config());
				$equipment_type = $model->create($equipment_type);
				ResponseHandler::render($equipment_type, $transformer);
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
