<?php

require '../../../src/bootstrap.php';

use Portalbox\ResponseHandler;
use Portalbox\Service\EquipmentService;
use Portalbox\Transform\EquipmentTransformer;

try {
	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET':		// List/Read
			if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
				$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
				if ($id === false) {
					throw new InvalidArgumentException('The equipment id must be specified as an integer');
				}

				$service = $container->get(EquipmentService::class);
				$equipment = $service->read($id);
				ResponseHandler::render($equipment, new EquipmentTransformer());
			} else { // List
				$service = $container->get(EquipmentService::class);
				$equipment = $service->readAll($_GET);
				ResponseHandler::render($equipment, new EquipmentTransformer());
			}
			break;
		case 'POST':	// Update
			if(!isset($_GET['id']) || empty($_GET['id'])) {
				throw new InvalidArgumentException('You must specify the equipment to modify via the id param');
			}

			$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
			if ($id === false) {
				throw new InvalidArgumentException('You must specify the equipment to modify via the id param');
			}

			$service = $container->get(EquipmentService::class);
			$equipment = $service->update($id, 'php://input');
			ResponseHandler::render($equipment, new EquipmentTransformer());
			break;
		case 'PUT':		// Create
			$service = $container->get(EquipmentService::class);
			$equipment = $service->create('php://input');
			ResponseHandler::render($equipment, new EquipmentTransformer());
			break;
		case 'DELETE':	// Delete
			// intentional fall through, deletion not allowed
		default:
			http_response_code(405);
			die('We were unable to understand your request.');
	}
} catch(Throwable $t) {
	ResponseHandler::setResponseCode($t);
	$message = $t->getMessage();
	if (empty($message)) {
		$message = ResponseHandler::GENERIC_ERROR_MESSAGE;
	}
	die($message);
}
