<?php

require '../../src/autoload.php';

use Portalbox\Config;
use Portalbox\ResponseHandler;
use Portalbox\Entity\Permission;
use Portalbox\Exception\NotFoundException;
use Portalbox\Model\EquipmentModel;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Model\LocationModel;
use Portalbox\Query\EquipmentQuery;
use Portalbox\Session\Session;
use Portalbox\Transform\EquipmentTransformer;

$session = new Session();

try {
	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET':		// List/Read
			if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
				// check authorization
				$session->require_authorization(Permission::READ_EQUIPMENT);

				$model = new EquipmentModel(Config::config());
				$equipment = $model->read($_GET['id']);
				if(!$equipment) {
					throw new NotFoundException('We have no record of that equipment');
				}

				$transformer = new EquipmentTransformer();
				ResponseHandler::render($equipment, $transformer);
			} else { // List
				// no authorization check as unauthenticated users may use

				$model = new EquipmentModel(Config::config());
				$query = new EquipmentQuery();
				if(isset($_GET['location_id']) && !empty($_GET['location_id'])) {
					$query->set_location_id($_GET['location_id']);
				} else if(isset($_GET['location']) && !empty($_GET['location'])) {
					$query->set_location($_GET['location']);
				}
				if(isset($_GET['type']) && !empty($_GET['type'])) {
					$query->set_type($_GET['type']);
				}
				if(
					!isset($_GET['include_out_of_service'])
					|| empty($_GET['include_out_of_service'])
				) {
					$query->set_exclude_out_of_service(true);
				}

				$equipment = $model->search($query);
				$transformer = new EquipmentTransformer();
				ResponseHandler::render($equipment, $transformer);
			}
			break;
		case 'POST':	// Update
			// validate that we have an oid
			if(!isset($_GET['id']) || empty($_GET['id'])) {
				throw new InvalidArgumentException('You must specify the equipment to modify via the id param');
			}

			// check authorization
			$session->require_authorization(Permission::MODIFY_EQUIPMENT);

			$data = json_decode(file_get_contents('php://input'), TRUE);
			if($data === null) {
				throw new InvalidArgumentException(json_last_error_msg());
			}

			$transformer = new EquipmentTransformer();
			$equipment = $transformer->deserialize($data);
			$equipment->set_id($_GET['id']);
			$model = new EquipmentModel(Config::config());
			$equipment = $model->update($equipment);
			ResponseHandler::render($equipment, $transformer);
			break;
		case 'PUT':		// Create
			// check authorization
			$session->require_authorization(Permission::CREATE_EQUIPMENT);

			$data = json_decode(file_get_contents('php://input'), TRUE);
			if($data === null) {
				throw new InvalidArgumentException(json_last_error_msg());
			}

			$transformer = new EquipmentTransformer();
			$equipment = $transformer->deserialize($data);
			$model = new EquipmentModel(Config::config());
			$equipment = $model->create($equipment);
			ResponseHandler::render($equipment, $transformer);
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
