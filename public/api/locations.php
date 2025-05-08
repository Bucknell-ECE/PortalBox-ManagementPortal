<?php

require '../../src/autoload.php';

use Portalbox\Config;
use Portalbox\ResponseHandler;
use Portalbox\Session;

use Portalbox\Entity\Permission;

use Portalbox\Model\LocationModel;

//use Portalbox\Query\LocationQuery;

use Portalbox\Transform\LocationTransformer;

// switch on the request method
switch($_SERVER['REQUEST_METHOD']) {
	case 'GET':		// List/Read
		if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
			// check authorization
			Session::require_authorization(Permission::READ_LOCATION);

			try {
				$model = new LocationModel(Config::config());
				$location = $model->read($_GET['id']);
				if($location) {
					$transformer = new LocationTransformer();
					ResponseHandler::render($location, $transformer);
				} else {
					http_response_code(404);
					die('We have no record of that location');
				}
			} catch(Exception $e) {
				http_response_code(500);
				die('We experienced issues communicating with the database');
			}
		} else { // List
			// check authorization
			Session::require_authorization(Permission::LIST_LOCATIONS);

			try {
				$model = new LocationModel(Config::config());
				$locations = $model->search();
				$transformer = new LocationTransformer();
				ResponseHandler::render($locations, $transformer);
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
			die('You must specify the location to modify via the id param');
		}

		// check authorization
		Session::require_authorization(Permission::MODIFY_LOCATION);

		$data = json_decode(file_get_contents('php://input'), TRUE);
		if(NULL !== $data) {
			try {
				$transformer = new LocationTransformer();
				$location = $transformer->deserialize($data);
				$location->set_id($_GET['id']);
				$model = new LocationModel(Config::config());
				$location = $model->update($location);
				ResponseHandler::render($location, $transformer);
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
		Session::require_authorization(Permission::CREATE_LOCATION);

		$data = json_decode(file_get_contents('php://input'), TRUE);
		if(NULL !== $data) {
			try {
				$transformer = new LocationTransformer();
				$location = $transformer->deserialize($data);
				$model = new LocationModel(Config::config());
				$location = $model->create($location);
				ResponseHandler::render($location, $transformer);
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
