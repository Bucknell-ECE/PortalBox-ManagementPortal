<?php

require '../../src/autoload.php';

use Portalbox\Config;
use Portalbox\ResponseHandler;
use Portalbox\Model\CardTypeModel;
use Portalbox\Service\CardTypeService;
use Portalbox\Session\Session;
use Portalbox\Transform\CardTypeTransformer;

$session = new Session();

try {
	//switch on the request method
	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET':     // List
			$service = new CardTypeService(
				$session,
				new CardTypeModel(Config::config()),
			);

			$card_types = $service->readAll();
			$transformer = new CardTypeTransformer();
			ResponseHandler::render($card_types, $transformer);
		break;
	}
} catch (AuthenticationException $ae) {
	http_response_code(401);
	die($session->ERROR_NOT_AUTHENTICATED);
} catch (AuthorizationException $aue) {
	http_response_code(403);
	die($aue->getMessage());
} catch (Throwable $t) {
	http_response_code(500);
	$message = $t->getMessage();
	if (empty($message)) {
		$message = 'We experienced issues communicating with the database';
	}
	die($message);
}
