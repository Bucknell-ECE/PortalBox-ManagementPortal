<?php

require '../../src/autoload.php';

use Portalbox\Config;
use Portalbox\ResponseHandler;
use Portalbox\Model\CardTypeModel;
use Portalbox\Service\CardTypeService;
use Portalbox\Session\Session;
use Portalbox\Transform\CardTypeTransformer;

$session = new Session();

try {
	//switch on the request method
	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET':     // List
			$service = new CardTypeService(
				$session,
				new CardTypeModel(Config::config()),
			);

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