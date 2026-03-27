<?php

require '../../../src/bootstrap.php';

use Portalbox\ResponseHandler;
use Portalbox\Service\PaymentService;
use Portalbox\Transform\PaymentTransformer;

try {
	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET':		// List/Read
			if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
				$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
				if ($id === false) {
					throw new InvalidArgumentException('The payment id must be specified as an integer');
				}

				$service = $container->get(PaymentService::class);
				$payment = $service->read($id);
				ResponseHandler::render($payment, new PaymentTransformer());
			} else { // List
				$service = $container->get(PaymentService::class);
				$payment = $service->readAll($_GET);
				ResponseHandler::render($payment, new PaymentTransformer());
			}
			break;
		case 'POST':	// Update
			if(!isset($_GET['id']) || empty($_GET['id'])) {
				throw new InvalidArgumentException('You must specify the payment to modify via the id param');
			}

			$id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
			if ($id === false) {
				throw new InvalidArgumentException('You must specify the payment to modify via the id param');
			}

			$service = $container->get(PaymentService::class);
			$payment = $service->update($id, 'php://input');
			ResponseHandler::render($payment, new PaymentTransformer());
			break;
		case 'PUT':		// Create
			$service = $container->get(PaymentService::class);
			$payment = $service->create('php://input');
			ResponseHandler::render($payment, new PaymentTransformer());
			break;
		case 'DELETE':	// Delete
			// intentional fall through, deletion not allowed, but maybe it should be?
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
