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


	if((include_once '../lib/EncodeOutput.php') === FALSE) {
		header('HTTP/1.0 500 Internal Server Error');
		die('We were unable to load some dependencies. Please ask your server administrator to investigate');
	}

	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET':
			$connection = DB::getConnection();
			$sql = 'SELECT * FROM charge_policies';
			$query = $connection->prepare($sql);
			if($query->execute()) {
				$policies = $query->fetchAll(\PDO::FETCH_ASSOC);
				render_json($policies);
			}
			break;
		default: // policies is read only
			header('HTTP/1.0 405 Method Not Allowed');
			die('We were unable to understand your request.');
	}
?>