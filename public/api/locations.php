<?php
	/**
	 * validate check that the paramter is an associative array with non empty
	 * values for the 'name' key and if all is well returns but if a check fails;
	 * the proper HTTP response is emitted and execution is halted.
	 */
	function validate($location) {
		if(!is_array($location)) {
			header('HTTP/1.0 500 Internal Server Error');
			die('We seem to have encountered an unexpected difficulty. Please ask your server administrator to investigate');
		}
		if(!array_key_exists('name', $location) || empty($location['name'])) {
			header('HTTP/1.0 400 Bad Request');
			die('You must specify the location\'s name');
		}
	}

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
						if(JSON_ERROR_NONE != json_last_error()) {
							header('HTTP/1.0 500 Internal Server Error');
							die(json_last_error_msg());
						}
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
					if(JSON_ERROR_NONE != json_last_error()) {
						header('HTTP/1.0 500 Internal Server Error');
						die(json_last_error_msg());
					}
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
					if(JSON_ERROR_NONE != json_last_error()) {
						header('HTTP/1.0 500 Internal Server Error');
						die(json_last_error_msg());
					}
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
					if(JSON_ERROR_NONE != json_last_error()) {
						header('HTTP/1.0 500 Internal Server Error');
						die(json_last_error_msg());
					}
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