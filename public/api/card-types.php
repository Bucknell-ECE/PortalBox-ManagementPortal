<?php

require '../../src/autoload.php';

use Portalbox\Config;
use Portalbox\ResponseHandler;
use Portalbox\Entity\Permission;
use Portalbox\Model\CardTypeModel;
use Portalbox\Session\Session;
use Portalbox\Transform\CardTypeTransformer;

$session = new Session();

try {
	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET':     // List
			$session->require_authorization(Permission::LIST_CARD_TYPES);

			$model = new CardTypeModel(Config::config());
			$card_types = $model->search();
			$transformer = new CardTypeTransformer();
			ResponseHandler::render($card_types, $transformer);
		break;
	}
} catch(Throwable $t) {
	ResponseHandler::setResponseCode($t);
	$message = $t->getMessage();
	if (empty($message)) {
		$message = ResponseHandler::GENERIC_ERROR_MESSAGE;
	}
	die($message);
}
