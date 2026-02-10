<?php

require '../../../src/bootstrap.php';

use Portalbox\ResponseHandler;
use Portalbox\Service\BadgeRuleService;

try {
	$service = $container->get(BadgeRuleService::class);
	$images = $service->getBadgeImages();
	header('Content-Type: application/json');
	echo json_encode($images);
} catch(Throwable $t) {
	ResponseHandler::setResponseCode($t);
	$message = $t->getMessage();
	if (empty($message)) {
		$message = ResponseHandler::GENERIC_ERROR_MESSAGE;
	}
	die($message);
}
