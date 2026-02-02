<?php

require '../../src/bootstrap.php';

use Portalbox\ResponseHandler;
use Portalbox\Service\CardTypeService;
use Portalbox\Session;
use Portalbox\Transform\CardTypeTransformer;

try {
	//switch on the request method
	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET':     // List
			$service = $container->get(CardTypeService::class);
			$card_types = $service->readAll();
			$transformer = new CardTypeTransformer();
			ResponseHandler::render($card_types, $transformer);
		break;
	}
} catch(Throwable $t) {
	ResponseHandler::setResponseCode($t);
	$message = $t->getMessage();
	if (empty($message)) {
		$message = ResponseHandler::GENERIC_ERROR_MESSAGE;
	}
	die($message);
}
