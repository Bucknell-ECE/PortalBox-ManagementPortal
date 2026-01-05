<?php

require '../../src/bootstrap.php';

use Portalbox\ResponseHandler;
use Portalbox\Service\RoleService;
use Portalbox\Transform\RoleTransformer;

try {
	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET':		// List/Read
			if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
				$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
				if ($id === false) {
					throw new InvalidArgumentException('The role id must be specified as an integer');
				}

				$service = $container->get(RoleService::class);
				$role = $service->read($id);
				ResponseHandler::render($role, new RoleTransformer());
			} else { // List
				$service = $container->get(RoleService::class);
				$roles = $service->readAll();
				ResponseHandler::render($roles, new RoleTransformer());
			}
			break;
		case 'POST':	// Update
			$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
			if ($id === false) {
				throw new InvalidArgumentException('You must specify the role to modify via the id param');
			}

			$service = $container->get(RoleService::class);
			$role = $service->update($id, 'php://input');
			ResponseHandler::render($role, new RoleTransformer());
			break;
		case 'PUT':		// Create
			$service = $container->get(RoleService::class);
			$role = $service->create('php://input');
			ResponseHandler::render($role, new RoleTransformer());
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
