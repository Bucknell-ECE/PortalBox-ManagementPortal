<?php

require '../../src/autoload.php';

use Portalbox\Config;
use Portalbox\ResponseHandler;
use Portalbox\Entity\Permission;
use Portalbox\Exception\NotFoundException;
use Portalbox\Model\CardModel;
use Portalbox\Query\CardQuery;
use Portalbox\Service\CardService;
use Portalbox\Session\Session;
use Portalbox\Transform\CardTransformer;

$session = new Session();

try {
	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET':		// List/Read
			if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
				$cardId = filter_var($_GET['id'], FILTER_VALIDATE_INT);
				if ($cardId === false) {
					throw new InvalidArgumentException('The card must be specified as an integer');
				}

				$service = new CardService(
					$session,
					new CardModel(Config::config())
				);
				$card = $service->read($cardId);
				$transformer = new CardTransformer();
				ResponseHandler::render($card, $transformer);
			} elseif(isset($_GET['search']) && !empty($_GET['search'])) {
				$cardId = filter_var($_GET['search'], FILTER_VALIDATE_INT);
				if ($cardId === false) {
					throw new InvalidArgumentException('The card must be specified as an integer');
				}

				$service = new CardService(
					$session,
					new CardModel(Config::config())
				);
				$card = $service->read($cardId);
				$transformer = new CardTransformer();
				ResponseHandler::render($card, $transformer);
			} else { // Lists
				$service = new CardService(
					$session,
					new CardModel(Config::config())
				);
				$cards = $service->readAll($_GET);
				$transformer = new CardTransformer();
				ResponseHandler::render($cards, $transformer);
			}
			break;
		case 'PUT':		// Create
			// check authorization
			$session->require_authorization(Permission::CREATE_CARD);

			$data = json_decode(file_get_contents('php://input'), TRUE);
			if($data === null) {
				throw new InvalidArgumentException(json_last_error_msg());
			}

			$transformer = new CardTransformer();
			$card = $transformer->deserialize($data);
			$model = new CardModel(Config::config());
			$card = $model->create($card);
			ResponseHandler::render($card, $transformer);
			break;
		case 'DELETE':	// Delete
			// intentional fall through, deletion not allowed
		case 'POST': // Update
			// intentional fall through, editing cards not allowed
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
