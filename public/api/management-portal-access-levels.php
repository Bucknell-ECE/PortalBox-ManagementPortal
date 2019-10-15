<?php
	// check authentication/authorization
	if((include_once '../lib/Security.php') === FALSE) {
		header('HTTP/1.0 500 Internal Server Error');
		die('We were unable to load some dependencies. Please ask your server administrator to investigate');
	}
	require_authorization('admin');

	// only authenticated users should reach this point
	if((include_once '../lib/Database.php') === FALSE) {
		header('HTTP/1.0 500 Internal Server Error');
		die('We were unable to load some dependencies. Please ask your server administrator to investigate');
	}

	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET':
			$connection = DB::getConnection();
			$sql = 'SELECT * FROM management_portal_access_levels';
			$query = $connection->prepare($sql);
			if($query->execute()) {
				$types = $query->fetchAll(\PDO::FETCH_ASSOC);
				echo json_encode($types);
				if(JSON_ERROR_NONE != json_last_error()) {
					header('HTTP/1.0 500 Internal Server Error');
					die(json_last_error_msg());
				}
			}
			break;
		default: // management_portal_access_levels is read only
			header('HTTP/1.0 405 Method Not Allowed');
			die('We were unable to understand your request.');
	}
?>