<?php
/**
 * Users authenticate to the API with an OAUTH token. Therefore we need to:
 * 
 * 1) Check for authorization header
 * 2) If header is set, validate the token
 * 3) If token is valid, check for user in db
 * 4) If user in db, start a session and stick user id in the session. Returning the user
 * 
 * We will do this is a procedural style since it is so linear.
 */

// Step 0 load up our code
require '../../src/autoload.php';

use Portalbox\Config;
use Portalbox\ResponseHandler;

use Portalbox\Model\UserModel;

use Portalbox\Query\UserQuery;

use Portalbox\Transform\UserTransformer;

// Step 1 check for AUTHORIZATION header
if(!array_key_exists('HTTP_AUTHORIZATION', $_SERVER)) {
	// If the user did not send an auth header, let them know it is required
	http_response_code(403);
	die('No Authorization header provided');	// should include link to docs
}

// Step 2 Validate AUTHORIZATION header
if(8 > strlen($_SERVER['HTTP_AUTHORIZATION']) || 0 != strcmp('Bearer ', substr($_SERVER['HTTP_AUTHORIZATION'], 0 , 7))) {
	http_response_code(400);
	die('Improperly formatted Authorization header. Please use "Bearer " + token syntax');
}
$token = substr($_SERVER['HTTP_AUTHORIZATION'], 7);
$url = 'https://www.googleapis.com/oauth2/v3/tokeninfo?id_token=' . $token;
$response = json_decode(file_get_contents($url), true);

// response should contain email and exp fields or error_description
if(array_key_exists('error_description', $response)) {
	// We are sure of the error and can inform the api consumer of the error
	http_response_code(403);
	die('Unable to verify authentication with OAuth provider (' . $response['error_description'] . ')');
} else if(array_key_exists('email', $response) && array_key_exists('exp', $response) && $response['exp'] > time()) {

	// Step 3 Check for user in db
	$model = new UserModel(Config::config());
	$query = (new UserQuery())->set_email($response['email']);
	$users = $model->search($query);
	if($users && 0 < count($users)) {

		// Step 4 user is found start a session and return the user
		if(session_status() !== PHP_SESSION_ACTIVE) {
			$success = session_start();
			if($success) {
				$_SESSION['user_id'] = $users[0]->id();
				$transformer = new UserTransformer();
				ResponseHandler::render($users[0], $transformer);
			} else {
				session_abort(); 
				http_response_code(500);
				die('We were unable to start your session. Please ask your server administrator to investigate');
			}
		} else {
			$transformer = new UserTransformer();
			ResponseHandler::render($users[0], $transformer);
		}
	} else {
		http_response_code(403);
		die('It does not appear you have been granted permission to use this system');
	}
} else {
	// Something unexpected happened; inform the user we can't let them in and don't know why
	http_response_code(403);
	die('Unable to verify authentication with OAuth provider (OAuth verification failed unexpectedly)');
}
