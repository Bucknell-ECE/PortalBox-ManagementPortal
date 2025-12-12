<?php

require '../../../src/bootstrap.php';

use Portalbox\Config;
use Portalbox\ResponseHandler;
use Portalbox\Service\LoggedEventService;

try {
	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET':
			$location_id = null;

			if(isset($_GET['id']) && !empty($_GET['id'])) {
				$location_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
				if ($location_id === false) {
					throw new InvalidArgumentException('The location must be specified as an integer');
				}
			}

			$service = $container->get(LoggedEventService::class);
			$counts = $service->getUsageStatsForLocation($location_id);
			header('Content-Type: application/json');
			echo json_encode($counts);
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
