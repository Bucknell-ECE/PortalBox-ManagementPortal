<?php

require '../../../src/bootstrap.php';

use Portalbox\ResponseHandler;
use Portalbox\Service\BadgeRuleService;
use Portalbox\Transform\BadgeRuleTransformer;

try {
	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET':		// List/Read
			if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
				$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
				if ($id === false) {
					throw new InvalidArgumentException('The badge rule id must be specified as an integer');
				}

				$service = $container->get(BadgeRuleService::class);
				$rule = $service->read($id);
				ResponseHandler::render($rule, new BadgeRuleTransformer());
			} else { // List
				$service = $container->get(BadgeRuleService::class);
				$rules = $service->readAll();
				ResponseHandler::render($rules, new BadgeRuleTransformer());
			}
			break;
		case 'POST':	// Update
			$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
			if ($id === false) {
				throw new InvalidArgumentException('You must specify the badge rule to modify via the id param');
			}

			$service = $container->get(BadgeRuleService::class);
			$rule = $service->update($id, 'php://input');
			ResponseHandler::render($rule, new BadgeRuleTransformer());
			break;
		case 'PUT':		// Create
			$service = $container->get(BadgeRuleService::class);
			$rule = $service->create('php://input');
			ResponseHandler::render($rule, new BadgeRuleTransformer());
			break;
		case 'DELETE':	// Delete
			$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
			if ($id === false) {
				throw new InvalidArgumentException('You must specify the badge rule to delete via the id param');
			}

			$service = $container->get(BadgeRuleService::class);
			$rule = $service->delete($id);
			ResponseHandler::render($rule, new BadgeRuleTransformer());
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