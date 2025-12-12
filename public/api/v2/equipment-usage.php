<?php

require '../../../src/bootstrap.php';

use Portalbox\Config;
use Portalbox\ResponseHandler;
use Portalbox\Service\LoggedEventService;

try {
	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET':
			if(isset($_GET['id']) && !empty($_GET['id'])) {
				$equipment_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
				if ($equipment_id === false) {
					throw new InvalidArgumentException('The equipment must be specified as an integer');
				}

				$service = $container->get(LoggedEventService::class);
				$counts = $service->getUsageStatsForEquipment($equipment_id);
				header('Content-Type: application/json');
				echo json_encode($counts);
			} else {
				throw new InvalidArgumentException('You must specify the equipment via the id param');
			}
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
