<?php

require '../../../src/bootstrap.php';

use Portalbox\ResponseHandler;
use Portalbox\Service\LocationService;
use Portalbox\Transform\LocationTransformer;

// switch on the request method
switch($_SERVER['REQUEST_METHOD']) {
	case 'GET':		// List/Read
		if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
			$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
			if ($id === false) {
				throw new InvalidArgumentException('The location id must be specified as an integer');
			}

			$service = $container->get(LocationService::class);
			$location = $service->read($id);
			ResponseHandler::render($location, new LocationTransformer());
		} else { // List
			$service = $container->get(LocationService::class);
			$locations = $service->readAll();
			ResponseHandler::render($locations, new LocationTransformer());
		}
		break;
	case 'POST':	// Update
		$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
			if ($id === false) {
				throw new InvalidArgumentException('You must specify the location to modify via the id param');
			}

			$service = $container->get(LocationService::class);
			$location = $service->update($id, 'php://input');
			ResponseHandler::render($location, new LocationTransformer());
		break;
	case 'PUT':		// Create
		$service = $container->get(LocationService::class);
		$location = $service->create('php://input');
		ResponseHandler::render($location, new LocationTransformer());
		break;
	case 'DELETE':	// Delete
		// intentional fall through, deletion not allowed
	default:
		http_response_code(405);
		die('We were unable to understand your request.');
}
