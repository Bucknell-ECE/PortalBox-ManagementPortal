<?php
	// check authentication
	if(session_status() !== PHP_SESSION_ACTIVE) {
		$success = session_start();
		if(!$success) {
			session_abort();
			header('HTTP/1.0 403 Not Authorized');
			die('You must request a session cookie from the api using the login.php endpoint before accessing this endpoint');
		}
	}
	if(!array_key_exists('user', $_SESSION)) {
		header('HTTP/1.0 403 Not Authorized');
		die('Your session is invalid. Perhaps you need to reauthenticate.');
	}
	//only admins can use this endpoint
	if(3 != $_SESSION['user']['management_portal_access_level_id']) {
		header('HTTP/1.0 403 Not Authorized');
		die('You have not been granted privileges for this data.');
	}

	// only authenticated users should reach this point
	if((include_once '../lib/Database.php') === FALSE) {
		header('HTTP/1.0 500 Internal Server Error');
		die('We were unable to load some dependencies. Please ask you server administrator to investigate');
	}

	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET':
			$connection = DB::getConnection();
			$sql = 'SELECT * FROM card_types';
			$query = $connection->prepare($sql);
			if($query->execute()) {
				$types = $query->fetchAll(\PDO::FETCH_ASSOC);
				echo json_encode($types);
			}
			break;
		default: // card_types is read only
			header('HTTP/1.0 405 Method Not Allowed');
			die('We were unable to understand your request.');
	}
?>