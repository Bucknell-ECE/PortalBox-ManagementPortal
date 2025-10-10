<?php

require '../../../src/bootstrap.php';

use Portalbox\Config;
use Portalbox\ResponseHandler;
use Portalbox\Service\EquipmentService;
use Portalbox\Transform\EquipmentTransformer;
use Portalbox\Transform\UserTransformer;

try {
	switch($_SERVER['REQUEST_METHOD']) {
		case 'PUT': // Activate Device
			if(!isset($_GET['mac']) || empty($_GET['mac'])) {
				throw new InvalidArgumentException('MAC address is required');
			}

			$service = $container->get(EquipmentService::class);
			$data = $service->activate($_GET['mac'], $_SERVER);
			$equipment = (new EquipmentTransformer())->serialize($data['equipment']);
			$user = (new UserTransformer())->serialize($data['user']);
			header('Content-Type: application/json');
			echo json_encode([
				'equipment' => $equipment,
				'user' => $user
			]);
			break;
		case 'POST': // Deactivate Device
			if(!isset($_GET['mac']) || empty($_GET['mac'])) {
				throw new InvalidArgumentException('MAC address is required');
			}

			$service = $container->get(EquipmentService::class);
			$equipment = $service->deactivate($_GET['mac'], $_SERVER);
			ResponseHandler::render($equipment, new EquipmentTransformer());
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