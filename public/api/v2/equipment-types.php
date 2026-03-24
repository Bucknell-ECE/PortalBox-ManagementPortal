<?php

require '../../../src/bootstrap.php';

use Portalbox\ResponseHandler;
use Portalbox\Service\EquipmentTypeService;
use Portalbox\Transform\EquipmentTypeTransformer;

try {
	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET':		// List/Read
			if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
				$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
				if ($id === false) {
					throw new InvalidArgumentException('The equipment type id must be specified as an integer');
				}

				$service = $container->get(EquipmentTypeService::class);
				$equipmentType = $service->read($id);
				ResponseHandler::render($equipmentType , new EquipmentTypeTransformer());
			} else { // List
				$service = $container->get(EquipmentTypeService::class);
				$equipmentTypes = $service->readAll();
				ResponseHandler::render($equipmentTypes, new EquipmentTypeTransformer());
			}
			break;
		case 'POST':	// Update
			if(!isset($_GET['id']) || empty($_GET['id'])) {
				throw new InvalidArgumentException('You must specify the equipment type to modify via the id param');
			}

			$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
			if ($id === false) {
				throw new InvalidArgumentException('The equipment type id must be specified as an integer');
			}

			$service = $container->get(EquipmentTypeService::class);
			$equipmentType = $service->update($id, 'php://input');
			ResponseHandler::render($equipmentType, new EquipmentTypeTransformer());
			break;
		case 'PUT':	// Create
			$service = $container->get(EquipmentTypeService::class);
			$equipmentType = $service->create('php://input');
			ResponseHandler::render($equipmentType, new EquipmentTypeTransformer());
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
