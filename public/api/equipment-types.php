<?php

require '../../src/autoload.php';

use Portalbox\Config;
use Portalbox\ResponseHandler;
use Portalbox\Entity\Permission;
use Portalbox\Exception\NotFoundException;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Service\EquipmentTypeService;
use Portalbox\Session\Session;
use Portalbox\Transform\EquipmentTypeTransformer;

$session = new Session();

try {
	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET':		// List/Read
			if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
				// check authorization
				$session->require_authorization(Permission::READ_EQUIPMENT_TYPE);

				$model = new EquipmentTypeModel(Config::config());
				$equipment_type = $model->read($_GET['id']);
				if(!$equipment_type) {
					throw new NotFoundException('We have no record of that equipment type');
				}

				$transformer = new EquipmentTypeTransformer();
				ResponseHandler::render($equipment_type, $transformer);
			} else { // List
				// check authorization
				$session->require_authorization(Permission::LIST_EQUIPMENT_TYPES);

				$model = new EquipmentTypeModel(Config::config());
				$equipment_types = $model->search();
				$transformer = new EquipmentTypeTransformer();
				ResponseHandler::render($equipment_types, $transformer);
			}
			break;
		case 'POST':	// Update
			if(!isset($_GET['id']) || empty($_GET['id'])) {
				throw new InvalidArgumentException('You must specify the equipment type to modify via the id param');
			}

			$equipmentTypeId = filter_var($_GET['id'], FILTER_VALIDATE_INT);
			if ($equipmentTypeId === false) {
				throw new InvalidArgumentException('The equipment type id must be specified as an integer');
			}

			$service = new EquipmentTypeService(
				$session,
				new EquipmentTypeModel(Config::config())
			);

			$equipmentType = $service->update($equipmentTypeId, 'php://input');
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
