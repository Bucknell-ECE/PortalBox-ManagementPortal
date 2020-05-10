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
			// check authentication
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
			try {
				$model = new EquipmentModel(Config::config());
				$query = (new EquipmentQuery());
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
		// check authorization
		Session::require_authorization(Permission::MODIFY_EQUIPMENT);

		// validate that we have an oid
		if(!isset($_GET['id']) || empty($_GET['id'])) {
			header('HTTP/1.0 400 Bad Request');
			die('You must specify the equipment to modify via the id param');
		}

		$equipment = json_decode(file_get_contents('php://input'), TRUE);
		if(NULL !== $equipment) {
			// validate equipment
			validate($equipment);
			$connection = DB::getConnection();
			$sql = 'SELECT id FROM equipment WHERE mac_address = :mac_address';
			$query = $connection->prepare($sql);
			$query->bindValue(':mac_address', $equipment['mac_address']);
			if($query->execute()) {
				$existing = $query->fetch(PDO::FETCH_ASSOC);
				if($existing && $existing['id'] != $_GET['id']) {
					header('HTTP/1.0 400 Bad Request');
					die('You must specify a mac_address not already in use by other equipment for the equipment');
				}
			} else {
				header('HTTP/1.0 500 Internal Server Error');
				die('We experienced issues communicating with the database');
			}

			// Save to the database
			$sql = 'UPDATE equipment SET name = :name, type_id = :type_id, mac_address = :mac_address, location_id = :location_id, timeout = :timeout, in_service = :in_service WHERE id = :id';
			$query = $connection->prepare($sql);
			$query->bindValue(':id', $_GET['id']);
			$query->bindValue(':name', $equipment['name']);
			$query->bindValue(':type_id', $equipment['type_id']);
			$query->bindValue(':mac_address', str_replace(array('-', ':'), '', $equipment['mac_address']));
			$query->bindValue(':location_id', $equipment['location_id']);
			$query->bindValue(':timeout', $equipment['timeout'], PDO::PARAM_INT);
			$query->bindValue(':in_service', $equipment['in_service'], PDO::PARAM_BOOL);
			if($query->execute()) {
				// success
				// most drivers do not report the number of rows on an UPDATE
				// We'll just return the equipment... but we'll update the value in the
				// id field for consistency
				$equipment['id'] = $_GET['id'];
				render_json($equipment);
			} else {
				header('HTTP/1.0 500 Internal Server Error');
				//die($query->errorInfo()[2]);
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

		try {
			$input = json_decode(file_get_contents('php://input'), TRUE);
			$equipment = Equipment::deserialize($input);
			$model = new EquipmentModel(Config::config());
			//$equipment = $model->update($equipment);
			$transformer = new EquipmentTransformer();
			ResponseHandler::render($equipment, $transformer);
		} catch(Exception $e) {
			header('HTTP/1.0 400 Bad Request');
			die($e->getMessage());
		}

		// if(NULL !== $equipment) {
		// 	// validate equipment
		// 	validate($equipment);
		// 	$connection = DB::getConnection();
		// 	$sql = 'SELECT id FROM equipment WHERE mac_address = :mac_address';
		// 	$query = $connection->prepare($sql);
		// 	$query->bindValue(':mac_address', $equipment['mac_address']);
		// 	if($query->execute()) {
		// 		$existing = $query->fetch(PDO::FETCH_ASSOC);
		// 		if($existing) {
		// 			header('HTTP/1.0 400 Bad Request');
		// 			die('You must specify a mac_address not already in use for the equipment');
		// 		}
		// 	} else {
		// 		header('HTTP/1.0 500 Internal Server Error');
		// 		die('We experienced issues communicating with the database');
		// 	}

		// 	// Save to the database
		// 	$sql = 'INSERT INTO equipment(name, type_id, mac_address, location_id, timeout, in_service) VALUES(:name, :type_id, :mac_address, :location_id, :timeout, :in_service)';
		// 	$query = $connection->prepare($sql);
		// 	$query->bindValue(':name', $equipment['name']);
		// 	$query->bindValue(':type_id', $equipment['type_id']);
		// 	$query->bindValue(':mac_address', str_replace(array('-', ':'), '', $equipment['mac_address']));
		// 	$query->bindValue(':location_id', $equipment['location_id']);
		// 	$query->bindValue(':timeout', $equipment['timeout'], PDO::PARAM_INT);
		// 	$query->bindValue(':in_service', $equipment['in_service'], PDO::PARAM_BOOL);
		// 	if($query->execute()) {
		// 		// success
		// 		// most drivers do not report the number of rows on an INSERT
		// 		// We'll return the equipment after adding/overwriting an id field
		// 		$equipment['id'] = $connection->lastInsertId('equipment_id_seq');
		// 		render_json($equipment);
		// 	} else {
		// 		header('HTTP/1.0 500 Internal Server Error');
		// 		die($query->errorInfo()[2]);
		// 		//die('We experienced issues communicating with the database');
		// 	}
		// } else {
		// 	header('HTTP/1.0 400 Bad Request');
		// 	die(json_last_error_msg());
		// }
		break;
	case 'DELETE':	// Delete
		// intentional fall through, deletion not allowed
	default:
		header('HTTP/1.0 405 Method Not Allowed');
		die('We were unable to understand your request.');
}
