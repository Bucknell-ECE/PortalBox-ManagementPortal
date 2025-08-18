<?php

require '../../src/autoload.php';

use Portalbox\Config;
use Portalbox\ResponseHandler;
use Portalbox\Entity\Permission;
use Portalbox\Exception\NotFoundException;
use Portalbox\Model\CardModel;
use Portalbox\Query\CardQuery;
use Portalbox\Session\Session;
use Portalbox\Transform\CardTransformer;

$session = new Session();

try {
	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET':		// List/Read
			if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
				$session->require_authorization(Permission::READ_CARD);

				$model = new CardModel(Config::config());
				$card = $model->read($_GET['id']);
				if(!$card) {
					throw new NotFoundException('We have no record of that card');
				}

				$transformer = new CardTransformer();
				ResponseHandler::render($card, $transformer);
			} elseif(isset($_GET['search']) && !empty($_GET['search'])) {
				$search_id = $_GET['search'];

				$session->require_authorization(Permission::READ_CARD);

				$model = new CardModel(Config::config());
				$query = (new CardQuery())->set_id($search_id);
				$cards = $model->search($query);

				$transformer = new CardTransformer();
				ResponseHandler::render($cards, $transformer);
			} else { // Lists
				$user_id = NULL;

				// check authorization
				if($session->check_authorization(Permission::LIST_OWN_CARDS)) {
					if(!$session->check_authorization(Permission::LIST_CARDS)) {
						$user_id = $session->get_authenticated_user()->id();
					}
				} else {
					$session->require_authorization(Permission::LIST_CARDS);
				}

				$model = new CardModel(Config::config());
				$query = new CardQuery();
				if(isset($_GET['equipment_type_id']) && !empty($_GET['equipment_type_id'])) {
					$query->set_equipment_type_id($_GET['equipment_type_id']);
				}
				if(NULL !== $user_id) {
					$query->set_user_id($user_id);
				} else if(isset($_GET['user_id']) && !empty($_GET['user_id'])) {
					$query->set_user_id($_GET['user_id']);
				}

				$cards = $model->search($query);
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
