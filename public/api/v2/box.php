<?php

require '../../../src/bootstrap.php';

use Portalbox\Config;
use Portalbox\ResponseHandler;
use Portalbox\Service\EquipmentService;
use Portalbox\Transform\EquipmentTransformer;

try {
	switch($_SERVER['REQUEST_METHOD']) {
		case 'PUT': // Register Device
			if(!isset($_GET['mac']) || empty($_GET['mac'])) {
				throw new InvalidArgumentException('MAC address is required');
			}

			$service = $container->get(EquipmentService::class);
			$equipment = $service->register($_GET['mac'], $_SERVER);
			$transformer = new EquipmentTransformer();
			ResponseHandler::render($equipment, $transformer);
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
