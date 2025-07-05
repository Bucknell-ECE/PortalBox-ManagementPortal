<?php

require '../../src/autoload.php';

use Portalbox\Config;
use Portalbox\ResponseHandler;
use Portalbox\Entity\Permission;
use Portalbox\Exception\NotFoundException;
use Portalbox\Model\RoleModel;
use Portalbox\Model\UserModel;
use Portalbox\Query\UserQuery;
use Portalbox\Service\UserService;
use Portalbox\Session\Session;
use Portalbox\Transform\AuthorizationsTransformer;
use Portalbox\Transform\UserTransformer;

$session = new Session();

// switch on the request method
switch($_SERVER['REQUEST_METHOD']) {
	case 'GET':		// List/Read
		if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
			$user_id = $_GET['id'];
			// check authorization
			if($session->check_authorization(Permission::READ_OWN_USER)) {
				if((int)$user_id !== (int)$session->get_authenticated_user()->id()) {
					$session->require_authorization(Permission::READ_USER);
				}
			} else {
				$session->require_authorization(Permission::READ_USER);
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
			$session->require_authorization(Permission::LIST_USERS);

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
		if(!($session->check_authorization(Permission::CREATE_EQUIPMENT_AUTHORIZATION) || $session->check_authorization(Permission::DELETE_EQUIPMENT_AUTHORIZATION))) {
			$session->require_authorization(Permission::MODIFY_USER);
		}

		if(NULL !== $data) {
			try {
				$service = new UserService(
					new RoleModel(Config::config()),
					new UserModel(Config::config())
				);
				$user = $service->patch(intval($_GET['id']), 'php://input');
				$userTransformer = new UserTransformer();
				ResponseHandler::render($user, $userTransformer);
			} catch (NotFoundException $nfe) {
				http_response_code(404);
				die($nfe->getMessage());
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
		$session->require_authorization(Permission::MODIFY_USER);

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
		$session->require_authorization(Permission::CREATE_USER);

		switch($_SERVER["CONTENT_TYPE"]) {
			case 'application/json':
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
			case 'text/csv':
				try {
					$service = new UserService(
						new RoleModel(Config::config()),
						new UserModel(Config::config())
					);
					$users = $service->import('php://input');
					echo count($users);
				} catch(\Throwable $e) {
					http_response_code(500);
					echo $e->getMessage();
				}
				break;
			default:
				http_response_code(415);
				die('We were unable to understand your request.');
		}
		break;
	case 'DELETE':	// Delete
		// intentional fall through, deletion not allowed
	default:
		http_response_code(405);
		die('We were unable to understand your request.');
}