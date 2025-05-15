<?php

require '../../src/autoload.php';

use Portalbox\Config;
use Portalbox\ResponseHandler;
use Portalbox\Session;

use Portalbox\Entity\Permission;

use Portalbox\Model\CardModel;

use Portalbox\Query\CardQuery;

use Portalbox\Transform\CardTransformer;


// switch on the request method
switch($_SERVER['REQUEST_METHOD']) {
	case 'GET':		// List/Read
		if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
			Session::require_authorization(Permission::READ_CARD);

			try {
				$model = new CardModel(Config::config());
				$card = $model->read($_GET['id']);
				if($card) {
					$transformer = new CardTransformer();
					ResponseHandler::render($card, $transformer);
				} else {
					http_response_code(404);
					die('We have no record of that card');
				}
			} catch(Exception $e) {
				http_response_code(500);
				die('We experienced issues communicating with the database');
			}
		} elseif(isset($_GET['search']) && !empty($_GET['search'])) {
			$search_id = $_GET['search'];

			Session::require_authorization(Permission::READ_CARD);

			try {
				$model = new CardModel(Config::config());
				$query = (new CardQuery())->set_id($search_id);
				$cards = $model->search($query);

				$transformer = new CardTransformer();
				ResponseHandler::render($cards, $transformer);
			} catch(Exception $e) {
				http_response_code(500);
				die('We experienced issues communicating with the database');
			}
		} else { // Lists
			$user_id = NULL;

			// check authorization
			if(Session::check_authorization(Permission::LIST_OWN_CARDS)) {
				if(!Session::check_authorization(Permission::LIST_CARDS)) {
					$user_id = Session::get_authenticated_user()->id();
				}
			} else {
				Session::require_authorization(Permission::LIST_CARDS);
			}

			try {
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
			} catch(Exception $e) {
				http_response_code(500);
				die('We experienced issues communicating with the database');
			}
		}
		break;
	case 'PUT':		// Create
		// check authorization
		Session::require_authorization(Permission::CREATE_CARD);

		$data = json_decode(file_get_contents('php://input'), TRUE);
		if(NULL !== $data) {
			try {
				$transformer = new CardTransformer();
				$card = $transformer->deserialize($data);
				$model = new CardModel(Config::config());
				$card = $model->create($card);
				ResponseHandler::render($card, $transformer);
			} catch(InvalidArgumentException $iae) {
				http_response_code(400);
				die($iae->getMessage());
			} catch(Exception $e) {
				http_response_code(500);
				die('We experienced issues communicating with the database');
			}
		} else {
			http_response_code(400);
			die(json_last_error_msg());
		}
		break;
	case 'DELETE':	// Delete
		// intentional fall through, deletion not allowed
	case 'POST': // Update
		// intentional fall through, editing cards not allowed
	default:
		http_response_code(405);
		die('We were unable to understand your request.');
}
