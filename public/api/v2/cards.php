<?php

require '../../../src/bootstrap.php';

use Portalbox\ResponseHandler;
use Portalbox\Service\CardService;
use Portalbox\Transform\CardTransformer;

try {
	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET':		// List/Read
			if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
				$cardId = filter_var($_GET['id'], FILTER_VALIDATE_INT);
				if ($cardId === false) {
					throw new InvalidArgumentException('The card must be specified as an integer');
				}

				$service = $container->get(CardService::class);
				$card = $service->read($cardId);
				ResponseHandler::render($card, new CardTransformer());
			} else { // Lists
				$service = $container->get(CardService::class);
				$cards = $service->readAll($_GET);
				ResponseHandler::render($cards, new CardTransformer());
			}
			break;
		case 'PUT':		// Create
			$service = $container->get(CardService::class);
			$card = $service->create('php://input');
			ResponseHandler::render($card, new CardTransformer());
			break;
		case 'DELETE':	// Delete
			// intentional fall through, deletion not allowed
		case 'POST': // Update
			// intentional fall through, editing cards not allowed
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
