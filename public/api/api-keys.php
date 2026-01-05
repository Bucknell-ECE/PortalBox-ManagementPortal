<?php

require '../../src/bootstrap.php';

use Portalbox\ResponseHandler;
use Portalbox\Service\APIKeyService;
use Portalbox\Transform\APIKeyTransformer;

try {
	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET':		// List/Read
			if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
				$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
				if ($id === false) {
					throw new InvalidArgumentException('The API key id must be specified as an integer');
				}

				$service = $container->get(APIKeyService::class);
				$key = $service->read($id);
				ResponseHandler::render($key, new APIKeyTransformer());
			} else { // List
				$service = $container->get(APIKeyService::class);
				$keys = $service->readAll($_GET);
				ResponseHandler::render($keys, new APIKeyTransformer());
			}
			break;
		case 'POST':	// Update
			$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
			if ($id === false) {
				throw new InvalidArgumentException('You must specify the API key to modify via the id param');
			}

			$service = $container->get(APIKeyService::class);
			$key = $service->update($id, 'php://input');
			ResponseHandler::render($key, new APIKeyTransformer());
			break;
		case 'PUT':		// Create
			$service = $container->get(APIKeyService::class);
			$key = $service->create('php://input');
			ResponseHandler::render($key, new APIKeyTransformer());
			break;
		case 'DELETE':	// Delete
			$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
			if ($id === false) {
				throw new InvalidArgumentException('You must specify the API key to delete via the id param');
			}

			$service = $container->get(APIKeyService::class);
			$key = $service->delete($id);
			ResponseHandler::render($key, new APIKeyTransformer());
			break;
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
