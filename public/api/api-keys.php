<?php

require '../../src/autoload.php';

use Portalbox\Config;
use Portalbox\ResponseHandler;
use Portalbox\Session;

use Portalbox\Entity\Permission;

use Portalbox\Model\APIKeyModel;
use Portalbox\Query\APIKeyQuery;
use Portalbox\Transform\APIKeyTransformer;

// switch on the request method
switch($_SERVER['REQUEST_METHOD']) {
	case 'GET':		// List/Read
		if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
			// check authorization
			Session::require_authorization(Permission::READ_API_KEY);

			try {
				$model = new APIKeyModel(Config::config());
				$key = $model->read($_GET['id']);
				if($key) {
					$transformer = new APIKeyTransformer();
					ResponseHandler::render($key, $transformer);
				} else {
					http_response_code(404);
					die('We have no record of that API key');
				}
			} catch(Exception $e) {
				http_response_code(500);
				die('We experienced issues communicating with the database');
			}
		} else { // List
			// check authorization
			Session::require_authorization(Permission::LIST_API_KEYS);

			try {
				$model = new APIKeyModel(Config::config());
				$query = new APIKeyQuery();
				if(isset($_GET['token']) && !empty($_GET['token'])) {
					$query->set_token($_GET['token']);
				}
				$keys = $model->search($query);
				$transformer = new APIKeyTransformer();
				ResponseHandler::render($keys, $transformer);
			} catch(Exception $e) {
				http_response_code(500);
				die($e->getMessage());
				die('We experienced issues communicating with the database');
			}
		}
		break;
	case 'POST':	// Update
		// validate that we have an oid
		if(!isset($_GET['id']) || empty($_GET['id'])) {
			http_response_code(400);
			die('You must specify the API key to modify via the id param');
		}

		// check authorization
		Session::require_authorization(Permission::MODIFY_API_KEY);

		$data = json_decode(file_get_contents('php://input'), TRUE);
		if(NULL !== $data) {
			try {
				$transformer = new APIKeyTransformer();
				$key = $transformer->deserialize($data);
				$key->set_id($_GET['id']);
				$model = new APIKeyModel(Config::config());
				$key = $model->update($key);
				ResponseHandler::render($key, $transformer);
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
		Session::require_authorization(Permission::CREATE_API_KEY);

		$data = json_decode(file_get_contents('php://input'), TRUE);
		if(NULL !== $data) {
			try {
				$transformer = new APIKeyTransformer();
				$key = $transformer->deserialize($data);
				$model = new APIKeyModel(Config::config());
				$key = $model->create($key);
				ResponseHandler::render($key, $transformer);
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
		// validate that we have an oid
		if(!isset($_GET['id']) || empty($_GET['id'])) {
			http_response_code(400);
			die('You must specify the api key to delete via the id param');
		}

		// check authorization
		Session::require_authorization(Permission::DELETE_API_KEY);

		try {
			$model = new APIKeyModel(Config::config());
			$key = $model->delete($_GET['id']);
			if($key) {
				$transformer = new APIKeyTransformer();
				ResponseHandler::render($key, $transformer);
			} else {
				http_response_code(404);
				die('We have no record of that API key');
			}
		} catch(Exception $e) {
			http_response_code(500);
			die('We experienced issues communicating with the database');
		}
		break;
	default:
		http_response_code(405);
		die('We were unable to understand your request.');
}
