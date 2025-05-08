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
					http_response_code(404);
					die('We have no record of that equipment type');
				}
			} catch(Exception $e) {
				http_response_code(500);
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
				http_response_code(500);
				die('We experienced issues communicating with the database');
			}
		}
		break;
	case 'POST':	// Update
		// validate that we have an oid
		if(!isset($_GET['id']) || empty($_GET['id'])) {
			http_response_code(400);
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
			} catch(InvalidArgumentException $iae) {
				http_response_code(400);
				die($iae->getMessage());
			} catch(Exception $e) {
				error_log($e->getMessage());
				http_response_code(500);
				die('We experienced issues communicating with the database');
			}
		} else {
			http_response_code(400);
			die(json_last_error_msg());
		}
		break;
	case 'DELETE':	// Delete
		// intentional fall through, deletion not allowed
	default:
		http_response_code(405);
		die('We were unable to understand your request.');
}
