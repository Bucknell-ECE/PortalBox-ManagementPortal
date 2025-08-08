<?php

require '../../src/autoload.php';

use Portalbox\Config;
use Portalbox\ResponseHandler;
use Portalbox\Entity\Permission;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Exception\NotFoundException;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Model\RoleModel;
use Portalbox\Model\UserModel;
use Portalbox\Service\UserService;
use Portalbox\Session\Session;
use Portalbox\Transform\AuthorizationsTransformer;
use Portalbox\Transform\UserTransformer;

$session = new Session();

try {
	// switch on the request method
	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET': // List/Read
			if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
				$user_id = filter_var($_GET['id'], FILTER_VALIDATE_INT);
				if ($user_id === false) {
					throw new InvalidArgumentException('The user must be specified as an integer');
				}

				$service = new UserService(
					$session,
					new EquipmentTypeModel(Config::config()),
					new RoleModel(Config::config()),
					new UserModel(Config::config())
				);
				$user = $service->read($user_id);
				$transformer = new UserTransformer();
				ResponseHandler::render($user, $transformer);
			} else { // List
				$service = new UserService(
					$session,
					new EquipmentTypeModel(Config::config()),
					new RoleModel(Config::config()),
					new UserModel(Config::config())
				);
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

			$service = new UserService(
				$session,
				new EquipmentTypeModel(Config::config()),
				new RoleModel(Config::config()),
				new UserModel(Config::config())
			);
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
			// check authorization
			$session->require_authorization(Permission::CREATE_USER);

			switch($_SERVER['CONTENT_TYPE']) {
				case 'application/json':
					$data = json_decode(file_get_contents('php://input'), TRUE);
					if($data === null) {
						throw new InvalidArgumentException(json_last_error_msg());
					}

					$transformer = new UserTransformer();
					$user = $transformer->deserialize($data);
					$model = new UserModel(Config::config());
					$user = $model->create($user);
					ResponseHandler::render($user, $transformer);
					break;
				case 'text/csv':
					$service = new UserService(
						$session,
						new EquipmentTypeModel(Config::config()),
						new RoleModel(Config::config()),
						new UserModel(Config::config())
					);
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
} catch(InvalidArgumentException $iae) {
	http_response_code(400);
	die($iae->getMessage());
} catch (AuthenticationException $ae) {
	http_response_code(401);
	die($session->ERROR_NOT_AUTHENTICATED);
} catch (AuthorizationException $aue) {
	http_response_code(403);
	die(self::ERROR_NOT_AUTHORIZED);
} catch (NotFoundException $nfe) {
	http_response_code(404);
	die($nfe->getMessage());
} catch(Throwable $t) {
	http_response_code(500);
	$message = $t->getMessage();
	if (empty($message)) {
		$message = 'We experienced issues communicating with the database';
	}
	die($message);
}
