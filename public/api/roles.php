<?php

require '../../src/autoload.php';

use Portalbox\Config;
use Portalbox\ResponseHandler;
use Portalbox\Entity\Permission;
use Portalbox\Exception\NotFoundException;
use Portalbox\Model\RoleModel;
use Portalbox\Session\Session;
use Portalbox\Transform\RoleTransformer;

$session = new Session();

try {
	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET':		// List/Read
			if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
				// check authorization
				$session->require_authorization(Permission::READ_ROLE);

				$model = new RoleModel(Config::config());
				$role = $model->read($_GET['id']);
				if(!$role) {
					throw new NotFoundException('We have no record of that role');
				}

				$transformer = new RoleTransformer();
				ResponseHandler::render($role, $transformer);
			} else { // List
				// check authorization
				$session->require_authorization(Permission::LIST_ROLES);

				$model = new RoleModel(Config::config());
				$roles = $model->search();
				$transformer = new RoleTransformer();
				ResponseHandler::render($roles, $transformer);
			}
			break;
		case 'POST':	// Update
			// validate that we have an oid
			if(!isset($_GET['id']) || empty($_GET['id'])) {
				throw new InvalidArgumentException('You must specify the role to modify via the id param');
			}

			// check authorization
			$session->require_authorization(Permission::MODIFY_ROLE);

			$data = json_decode(file_get_contents('php://input'), TRUE);
			if($data === null) {
				throw new InvalidArgumentException(json_last_error_msg());
			}

			$transformer = new RoleTransformer();
			$role = $transformer->deserialize($data);
			$role->set_id($_GET['id']);
			$model = new RoleModel(Config::config());
			$role = $model->update($role);
			ResponseHandler::render($role, $transformer);
			break;
		case 'PUT':		// Create
			// check authorization
			$session->require_authorization(Permission::CREATE_ROLE);

			$data = json_decode(file_get_contents('php://input'), TRUE);
			if($data === null) {
				throw new InvalidArgumentException(json_last_error_msg());
			}

			$transformer = new RoleTransformer();
			$role = $transformer->deserialize($data);
			$model = new RoleModel(Config::config());
			$role = $model->create($role);
			ResponseHandler::render($role, $transformer);
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
