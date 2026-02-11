<?php

require '../../../src/bootstrap.php';

use Portalbox\ResponseHandler;
use Portalbox\Service\BadgeService;
use Portalbox\Transform\BadgeLevelTransformer;
use Portalbox\Transform\BadgeReportTransformer;

try {
	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET':
			if(isset($_GET['user_id']) && !empty($_GET['user_id'])) {	// Read
				$user_id = filter_var($_GET['user_id'] ?? '', FILTER_VALIDATE_INT);
				if ($user_id === false) {
					throw new InvalidArgumentException('The user id must be specified as an integer');
				}
				$service = $container->get(BadgeService::class);
				$badges = $service->getBadgesForUser($user_id);
				ResponseHandler::render($badges, new BadgeLevelTransformer());
			} else {
				$service = $container->get(BadgeService::class);
				$report = $service->getBadgesForActiveUsers();
				ResponseHandler::render($report, new BadgeReportTransformer());
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