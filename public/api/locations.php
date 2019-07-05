<?php
	/**
	 * validate check that the paramter is an associative array with non empty
	 * values for the 'name' key and if all is well returns but if a check fails;
	 * the proper HTTP response is emitted and execution is halted.
	 */
	function validate($location) {
		if(!is_array($location)) {
			header('HTTP/1.0 500 Internal Server Error');
			die('We seem to have encountered an unexpected difficulty. Please ask you server administrator to investigate');
		}
		if(!array_key_exists('name', $location) || empty($location['name'])) {
			header('HTTP/1.0 400 Bad Request');
			die('You must specify the location\'s name');
		}
	}

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

	// switch on the request method
	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET':		// List/Read
			if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
				$connection = DB::getConnection();
				$sql = 'SELECT id, name FROM locations WHERE id = :id';
				$query = $connection->prepare($sql);
				$query->bindValue(':id', $_GET['id']);
				if($query->execute()) {
					if($location = $query->fetch(\PDO::FETCH_ASSOC)) {
						echo json_encode($location);
					} else {
						header('HTTP/1.0 404 Not Found');
						die('We have no record of that location');
					}
				} else {
					header('HTTP/1.0 500 Internal Server Error');
					//die($query->errorInfo()[2]);
					die('We experienced issues communicating with the database');
				}
			} else { // List
				$connection = DB::getConnection();
				$sql = 'SELECT id, name FROM locations';
				$query = $connection->prepare($sql);
				if($query->execute()) {
					$locations = $query->fetchAll(\PDO::FETCH_ASSOC);
					echo json_encode($locations);
				} else {
					header('HTTP/1.0 500 Internal Server Error');
					//die($query->errorInfo()[2]);
					die('We experienced issues communicating with the database');
				}
			}
			break;
		case 'POST':	// Update
			// validate that we have an oid
			if(!isset($_GET['id']) || empty($_GET['id'])) {
				header('HTTP/1.0 400 Bad Request');
				die('You must specify the location to modify via the id param');
			}

			$location = json_decode(file_get_contents('php://input'), TRUE);
			if(NULL !== $location) {
				// validate location
				validate($location);

				// okay to save to DB
				$connection = DB::getConnection();
				$sql = 'UPDATE locations SET name = :name WHERE id = :id';
				$query = $connection->prepare($sql);
				$query->bindValue(':id', $_GET['id']);
				$query->bindValue(':name', $location['name']);
				if($query->execute()) {
					// success
					// most drivers do not report the number of rows on an UPDATE
					// We'll just return the location... but we'll update the value in the
					// id field for consistency
					$location['id'] = $_GET['id'];
					echo json_encode($location);
				} else {
					header('HTTP/1.0 500 Internal Server Error');
					//die($query->errorInfo()[2]);
					die('We experienced issues communicating with the database');
				}
			} else {
				header('HTTP/1.0 400 Bad Request');
				die(json_last_error_msg());
			}
			break;
		case 'PUT':		// Create
			$location = json_decode(file_get_contents('php://input'), TRUE);
			if(NULL !== $location) {
				// validate location
				validate($location);

				$connection = DB::getConnection();
				$sql = 'INSERT INTO locations(name) VALUES(:name)';
				$query = $connection->prepare($sql);
				$query->bindValue(':name', $location['name']);
				if($query->execute()) {
					// success
					// most drivers do not report the number of rows on an INSERT
					// We'll return the location after adding/overwriting an id field
					$location['id'] = $connection->lastInsertId('locations_id_seq');
					echo json_encode($location);
				} else {
					header('HTTP/1.0 500 Internal Server Error');
					//die($query->errorInfo()[2]);
					die('We experienced issues communicating with the database');
				}
			} else {
				header('HTTP/1.0 400 Bad Request');
				die(json_last_error_msg());
			}
			break;
		case 'DELETE':	// Delete
			// intentional fall through, deletion not allowed
		default:
			header('HTTP/1.0 405 Method Not Allowed');
			die('We were unable to understand your request.');
	}
	
?>