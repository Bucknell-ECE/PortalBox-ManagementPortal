<?php

require '../../../src/bootstrap.php';

use Portalbox\ResponseHandler;
use Portalbox\Service\ChargeService;
use Portalbox\Transform\ChargeTransformer;

try {
	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET':		// List/Read
			if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
				$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
				if ($id === false) {
					throw new InvalidArgumentException('The charge id must be specified as an integer');
				}

				$service = $container->get(ChargeService::class);
				$charge = $service->read($id);
				ResponseHandler::render($charge, new ChargeTransformer());
			} else { // List
				$service = $container->get(ChargeService::class);
				$charges = $service->readAll($_GET);
				ResponseHandler::render($charges, new ChargeTransformer());
			}
			break;
		case 'POST':	// Update
			// intentional fall through, charges are immutable, but maybe they should be?
		case 'PUT':		// Create
			// intentional fall through, creation not allowed via API, but maybe it should be?
		case 'DELETE':	// Delete
			// intentional fall through, deletion not allowed, but maybe it should be?
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
