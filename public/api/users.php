<?php

require '../../src/bootstrap.php';

use Portalbox\Config;
use Portalbox\ResponseHandler;
use Portalbox\Entity\Permission;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Model\RoleModel;
use Portalbox\Model\UserModel;
use Portalbox\Service\UserService;
use Portalbox\Session\Session;
use Portalbox\Transform\AuthorizationsTransformer;
use Portalbox\Transform\UserTransformer;

$session = new Session();

try {
	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET': // List/Read
			if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
				$user_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
				if ($user_id === false) {
					throw new InvalidArgumentException('The user must be specified as an integer');
				}

				$service = $container->get(UserService::class);
				$user = $service->read($user_id);
				$transformer = new UserTransformer();
				ResponseHandler::render($user, $transformer);
			} else { // List
				$service = $container->get(UserService::class);
				$users = $service->readAll($_GET);
				$transformer = new UserTransformer();
				ResponseHandler::render($users, $transformer);
			}
			break;
		case 'PATCH':
			// validate that we have an oid
			if(!isset($_GET['id']) || empty($_GET['id'])) {
				throw new InvalidArgumentException('You must specify the user to modify via the id param');
			}

			$service = $container->get(UserService::class);
			$user = $service->patch(intval($_GET['id']), 'php://input');
			$userTransformer = new UserTransformer();
			ResponseHandler::render($user, $userTransformer);
			break;
		case 'POST':	// Update
			// validate that we have an oid
			if(!isset($_GET['id']) || empty($_GET['id'])) {
				throw new InvalidArgumentException('You must specify the user to modify via the id param');
			}

			// check authorization
			$session->require_authorization(Permission::MODIFY_USER);

			$data = json_decode(file_get_contents('php://input'), TRUE);
			if($data === null) {
				throw new InvalidArgumentException(json_last_error_msg());
			}

			$transformer = new UserTransformer();
			$user = $transformer->deserialize($data);
			$user->set_id($_GET['id']);
			$model = new UserModel(Config::config());
			$user = $model->update($user);
			ResponseHandler::render($user, $transformer);
			break;
		case 'PUT':		// Create
			switch($_SERVER['CONTENT_TYPE']) {
				case 'application/json':
					$service = $container->get(UserService::class);
					$user = $service->create('php://input');
					ResponseHandler::render($user, new UserTransformer());
					break;
				case 'text/csv':
					$service = $container->get(UserService::class);
					$users = $service->import('php://input');
					echo count($users);
					break;
				default:
					http_response_code(415);
					die('We were unable to understand your request.');
			}
			break;
		case 'DELETE':	// Delete
			// intentional fall through, deletion not allowed
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
