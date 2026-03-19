<?php

require '../../../src/bootstrap.php';

use Portalbox\ResponseHandler;
use Portalbox\Service\LoggedEventService;
use Portalbox\Transform\LoggedEventTransformer;

try {
	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET':		// List
			$service = $container->get(LoggedEventService::class);
			$log = $service->readAll($_GET);
			ResponseHandler::render($log, new LoggedEventTransformer());
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
