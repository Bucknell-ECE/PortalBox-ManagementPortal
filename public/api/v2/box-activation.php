<?php

require '../../../src/bootstrap.php';

use Portalbox\Config;
use Portalbox\ResponseHandler;
use Portalbox\Service\PortalboxService;
use Portalbox\Transform\EquipmentTransformer;
use Portalbox\Transform\UserTransformer;

try {
	switch($_SERVER['REQUEST_METHOD']) {
		case 'PUT': // Activate Device
			if(!isset($_GET['mac']) || empty($_GET['mac'])) {
				throw new InvalidArgumentException('MAC address is required');
			}

			$service = $container->get(PortalboxService::class);
			$data = $service->activate($_GET['mac'], $_SERVER);
			$equipment = (new EquipmentTransformer())->serialize($data['equipment']);
			$user = (new UserTransformer())->serialize($data['user']);
			header('Content-Type: application/json');
			echo json_encode([
				'equipment' => $equipment,
				'user' => $user
			]);
			break;
		case 'POST': // Modify Session i.e. switch to proxy card, or switch to training
			if(!isset($_GET['mac']) || empty($_GET['mac'])) {
				throw new InvalidArgumentException('MAC address is required');
			}

			$service = $container->get(PortalboxService::class);
			$mode = $service->changeActivationSession('php://input', $_GET['mac'], $_SERVER);
			echo $mode->value;
			break;
		case 'DELETE': // Deactivate Device
			if(!isset($_GET['mac']) || empty($_GET['mac'])) {
				throw new InvalidArgumentException('MAC address is required');
			}

			$service = $container->get(PortalboxService::class);
			$service->deactivate($_GET['mac'], $_SERVER);
			// empty response body, the status code is sufficient
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
