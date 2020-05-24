<?php

require '../../src/autoload.php';

use Portalbox\Config;
use Portalbox\ResponseHandler;
use Portalbox\Session;

use Portalbox\Entity\Permission;

use Portalbox\Model\RoleModel;

use Portalbox\Transform\RoleTransformer;

// switch on the request method
switch($_SERVER['REQUEST_METHOD']) {
	case 'GET':		// List/Read
		if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
			// check authorization
			Session::require_authorization(Permission::READ_ROLE);

			try {
				$model = new RoleModel(Config::config());
				$role = $model->read($_GET['id']);
				if($role) {
					$transformer = new RoleTransformer();
					ResponseHandler::render($role, $transformer);
				} else {
					header('HTTP/1.0 404 Not Found');
					die('We have no record of that role');
				}
			} catch(Exception $e) {
				header('HTTP/1.0 500 Internal Server Error');
				die('We experienced issues communicating with the database');
			}
		} else { // List
			// check authorization
			Session::require_authorization(Permission::LIST_ROLES);

			try {
				$model = new RoleModel(Config::config());

				$roles = $model->search();
				$transformer = new RoleTransformer();
				ResponseHandler::render($roles, $transformer);
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
			die('You must specify the role to modify via the id param');
		}

		// check authorization
		Session::require_authorization(Permission::MODIFY_ROLE);

		$data = json_decode(file_get_contents('php://input'), TRUE);
		if(NULL !== $data) {
			try {
				$transformer = new RoleTransformer();
				$role = $transformer->deserialize($data);
				$role->set_id($_GET['id']);
				$model = new RoleModel(Config::config());
				$role = $model->update($role);
				ResponseHandler::render($role, $transformer);
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
		Session::require_authorization(Permission::CREATE_ROLE);

		$data = json_decode(file_get_contents('php://input'), TRUE);
		if(NULL !== $data) {
			try {
				$transformer = new RoleTransformer();
				$role = $transformer->deserialize($data);
				$model = new RoleModel(Config::config());
				$role = $model->create($role);
				ResponseHandler::render($role, $transformer);
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
