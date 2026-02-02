<?php

require '../../src/autoload.php';

use Portalbox\Config;
use Portalbox\ResponseHandler;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Service\EquipmentTypeService;
use Portalbox\Session;
use Portalbox\Transform\EquipmentTypeTransformer;

$session = new Session();

try {
	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET':		// List/Read
			if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
				$service = new EquipmentTypeService(
					$session,
					new EquipmentTypeModel(Config::config())
				);

				$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
				if ($id === false) {
					throw new InvalidArgumentException('The equipment type id must be specified as an integer');
				}

				$equipmentType = $service->read($id);
				$transformer = new EquipmentTypeTransformer();
				ResponseHandler::render($equipmentType , $transformer);
			} else { // List
				$service = new EquipmentTypeService(
					$session,
					new EquipmentTypeModel(Config::config())
				);

				$equipmentTypes = $service->readAll();
				$transformer = new EquipmentTypeTransformer();
				ResponseHandler::render($equipmentTypes, $transformer);
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

			$service = new EquipmentTypeService(
				$session,
				new EquipmentTypeModel(Config::config())
			);

			$equipmentType = $service->update($id, 'php://input');
			$transformer = new EquipmentTypeTransformer();
			ResponseHandler::render($equipmentType, $transformer);
			break;
		case 'PUT':	// Create
			$service = new EquipmentTypeService(
				$session,
				new EquipmentTypeModel(Config::config())
			);

			$equipmentType = $service->create('php://input');
			$transformer = new EquipmentTypeTransformer();
			ResponseHandler::render($equipmentType, $transformer);
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
