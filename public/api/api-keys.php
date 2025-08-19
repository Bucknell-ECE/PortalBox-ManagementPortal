<?php

require '../../src/autoload.php';

use Portalbox\Config;
use Portalbox\ResponseHandler;
use Portalbox\Entity\Permission;
use Portalbox\Exception\NotFoundException;
use Portalbox\Model\APIKeyModel;
use Portalbox\Query\APIKeyQuery;
use Portalbox\Session\Session;
use Portalbox\Transform\APIKeyTransformer;

$session = new Session();

try {
	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET':		// List/Read
			if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
				// check authorization
				$session->require_authorization(Permission::READ_API_KEY);

				$model = new APIKeyModel(Config::config());
				$key = $model->read($_GET['id']);

				if(!$key) {
					throw new NotFoundException('We have no record of that API key');
				}

				$transformer = new APIKeyTransformer();
				ResponseHandler::render($key, $transformer);
			} else { // List
				// check authorization
				$session->require_authorization(Permission::LIST_API_KEYS);

				$model = new APIKeyModel(Config::config());
				$query = new APIKeyQuery();
				if(isset($_GET['token']) && !empty($_GET['token'])) {
					$query->set_token($_GET['token']);
				}
				$keys = $model->search($query);
				$transformer = new APIKeyTransformer();
				ResponseHandler::render($keys, $transformer);
			}
			break;
		case 'POST':	// Update
			// validate that we have an oid
			if(!isset($_GET['id']) || empty($_GET['id'])) {
				throw new InvalidArgumentException('You must specify the API key to modify via the id param');
			}

			// check authorization
			$session->require_authorization(Permission::MODIFY_API_KEY);

			$data = json_decode(file_get_contents('php://input'), TRUE);
			if($data === null) {
				throw new InvalidArgumentException(json_last_error_msg());
			}

			$transformer = new APIKeyTransformer();
			$key = $transformer->deserialize($data);
			$key->set_id($_GET['id']);
			$model = new APIKeyModel(Config::config());
			$key = $model->update($key);
			ResponseHandler::render($key, $transformer);
			break;
		case 'PUT':		// Create
			// check authorization
			$session->require_authorization(Permission::CREATE_API_KEY);

			$data = json_decode(file_get_contents('php://input'), TRUE);
			if($data === null) {
				throw new InvalidArgumentException(json_last_error_msg());
			}

			$transformer = new APIKeyTransformer();
			$key = $transformer->deserialize($data);
			$model = new APIKeyModel(Config::config());
			$key = $model->create($key);
			ResponseHandler::render($key, $transformer);
			break;
		case 'DELETE':	// Delete
			// validate that we have an oid
			if(!isset($_GET['id']) || empty($_GET['id'])) {
				throw new InvalidArgumentException('You must specify the api key to delete via the id param');
			}

			// check authorization
			$session->require_authorization(Permission::DELETE_API_KEY);

			$model = new APIKeyModel(Config::config());
			$key = $model->delete($_GET['id']);
			if(!$key) {
				throw new NotFoundException('We have no record of that API key');
			}

			$transformer = new APIKeyTransformer();
			ResponseHandler::render($key, $transformer);
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
