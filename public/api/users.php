<?php

require '../../src/autoload.php';

use Portalbox\Config;
use Portalbox\ResponseHandler;
use Portalbox\Session;

use Portalbox\Entity\Permission;

use Portalbox\Model\UserModel;

use Portalbox\Query\UserQuery;

use Portalbox\Transform\AuthorizationsTransformer;
use Portalbox\Transform\UserTransformer;


// switch on the request method
switch($_SERVER['REQUEST_METHOD']) {
	case 'GET':		// List/Read
		if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
			$user_id = $_GET['id'];
			// check authorization
			if(Session::check_authorization(Permission::READ_OWN_USER)) {
				if((int)$user_id !== (int)Session::get_authenticated_user()->id()) {
					Session::require_authorization(Permission::READ_USER);
				}
			} else {
				Session::require_authorization(Permission::READ_USER);
			}

			try {
				$model = new UserModel(Config::config());
				$user = $model->read($user_id);
				if($user) {
					$transformer = new UserTransformer();
					ResponseHandler::render($user, $transformer);
				} else {
					http_response_code(404);
					die('We have no record of that user');
				}
			} catch(Exception $e) {
				http_response_code(500);
				die('We experienced issues communicating with the database');
			}
		} else { // List
			// check authorization
			Session::require_authorization(Permission::LIST_USERS);

			try {
				$model = new UserModel(Config::config());

				$query = new UserQuery();

				if(isset($_GET['include_inactive']) && !empty($_GET['include_inactive'])) {
					$include_inactive = $_GET['include_inactive'] === 'true' ? 1 : 0;
					$query->set_include_inactive($include_inactive);
				}
				if(isset($_GET['role_id']) && !empty($_GET['role_id'])) {
					$query->set_role_id($_GET['role_id']);
				}
				if(isset($_GET['name']) && !empty($_GET['name'])) {
					$query->set_name($_GET['name']);
				}
				if(isset($_GET['comment']) && !empty($_GET['comment'])) {
					$query->set_comment($_GET['comment']);
				}
				if(isset($_GET['email']) && !empty($_GET['email'])) {
					$query->set_email($_GET['email']);
				}
				if(isset($_GET['equipment_id']) && !empty($_GET['equipment_id'])) {
					$query->set_equipment_id($_GET['equipment_id']);
				}

				$users = $model->search($query);
				$transformer = new UserTransformer();
				ResponseHandler::render($users, $transformer);
			} catch(Exception $e) {
				http_response_code(500);
				die('We experienced issues communicating with the database');
			}
		}
		break;
	case 'PATCH':
		// validate that we have an oid
		if(!isset($_GET['id']) || empty($_GET['id'])) {
			http_response_code(400);
			die('You must specify the user to modify via the id param');
		}

		// check authorization
		if(!(Session::check_authorization(Permission::CREATE_EQUIPMENT_AUTHORIZATION) || Session::check_authorization(Permission::DELETE_EQUIPMENT_AUTHORIZATION))) {
			Session::require_authorization(Permission::MODIFY_USER);
		}

		$data = json_decode(file_get_contents('php://input'), TRUE);
		if(NULL !== $data) {
			try {
				$model = new UserModel(Config::config());
				$user = $model->read($_GET['id']);
				if($user) {
					$authTransformer = new AuthorizationsTransformer();
					$userTransformer = new UserTransformer();
					$authorizations = $authTransformer->deserialize($data);
					$user->set_authorizations($authorizations);
					$user = $model->update($user);
					ResponseHandler::render($user, $userTransformer);
				} else {
					http_response_code(404);
					die('We have no record of that user');
				}
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
	case 'POST':	// Update
		// validate that we have an oid
		if(!isset($_GET['id']) || empty($_GET['id'])) {
			http_response_code(400);
			die('You must specify the user to modify via the id param');
		}

		// check authorization
		Session::require_authorization(Permission::MODIFY_USER);

		$data = json_decode(file_get_contents('php://input'), TRUE);
		if(NULL !== $data) {
			try {
				$transformer = new UserTransformer();
				$user = $transformer->deserialize($data);
				$user->set_id($_GET['id']);
				$model = new UserModel(Config::config());
				$user = $model->update($user);
				ResponseHandler::render($user, $transformer);
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
		Session::require_authorization(Permission::CREATE_USER);

		$data = json_decode(file_get_contents('php://input'), TRUE);
		if(NULL !== $data) {
			try {
				$transformer = new UserTransformer();
				$user = $transformer->deserialize($data);
				$model = new UserModel(Config::config());
				$user = $model->create($user);
				ResponseHandler::render($user, $transformer);
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
		// intentional fall through, deletion not allowed
	default:
		http_response_code(405);
		die('We were unable to understand your request.');
}
