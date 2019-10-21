<?php
	if(!array_key_exists('HTTP_AUTHORIZATION', $_SERVER)) {
		// If the user did not send an auth header, let them know it is required
		header('HTTP/1.0 403 Not Authorized');
		die('No Authorization header provided');	 // should include link to docs
	}

	if(8 > strlen($_SERVER['HTTP_AUTHORIZATION']) || 0 != strcmp('Bearer ', substr($_SERVER['HTTP_AUTHORIZATION'], 0 , 7))) {
		header('HTTP/1.0 400 Bad Request');
		die('Improperly formatted Authorization header. Please use "Bearer " + token syntax');
	}
	$token = substr($_SERVER['HTTP_AUTHORIZATION'], 7);
	$url = 'https://www.googleapis.com/oauth2/v3/tokeninfo?id_token=' . $token;
	$response = json_decode(file_get_contents($url), true);

	// response should contain email and exp fields or error_description
	if(array_key_exists('error_description', $response)) {
		// We are sure of the error and can inform the api consumer of the error
		header('HTTP/1.0 403 Not Authorized');
		die('Unable to verify authentication with OAuth provider (' . $response['error_description'] . ')');
	} else if(array_key_exists('email', $response) && array_key_exists('exp', $response) && $response['exp'] > time()) {
		// We have a successful OAuth authentication, check that user is
		// registered with our system and has api permissions
		if((include_once '../lib/Database.php') === FALSE) {
			header('HTTP/1.0 500 Internal Server Error');
			die('We were unable to load some dependencies. Please ask your server administrator to investigate');
		}

		if((include_once '../lib/EncodeOutput.php') === FALSE) {
			header('HTTP/1.0 500 Internal Server Error');
			die('We were unable to load some dependencies. Please ask your server administrator to investigate');
		}

		$connection = DB::getConnection();
		$sql = 'SELECT * FROM users WHERE email = :email';
		$query = $connection->prepare($sql);
		$query->bindValue(':email', $response['email']);
		if($query->execute()) {
			$user = $query->fetch(\PDO::FETCH_ASSOC);
			// warning hardcoded value... management portal access level 1 := no access
			if($user && 1 < $user['management_portal_access_level_id']) {
				if(session_status() !== PHP_SESSION_ACTIVE) {
					$success = session_start();
					if($success) {
						// Successful login!!!
						$_SESSION['user'] = $user;
						render_json($user);
					} else {
						session_abort();
						header('HTTP/1.0 500 Internal Server Error');
						die('We were unable to start your session. Please ask your server administrator to investigate');
					}
				} else {
					render_json($user);
				}
			} else {
				header('HTTP/1.0 403 Not Authorized');
				die('It appears you have not been authorized for api access.');
			}
		} else {
			header('HTTP/1.0 500 Internal Server Error');
			die('We experienced issues communicating with the database');
		}
	} else {
		// Something unexpected happened; inform the user we can't let them in and don't know why
		header('HTTP/1.0 403 Not Authorized');
		die('Unable to verify authentication with OAuth provider (OAuth verification failed unexpectedly)');
	}
?>