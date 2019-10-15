<?php
	/**
	 * validate check that the paramter is an associative array with non empty
	 * values for the 'name' key. If all is well returns but if a check fails;
	 * the proper HTTP response is emitted and execution is halted.
	 */
	function validate($key) {
		if(!is_array($key)) {
			header('HTTP/1.0 500 Internal Server Error');
			die('We seem to have encountered an unexpected difficulty. Please ask your server administrator to investigate');
		}
		if(!array_key_exists('name', $key) || empty($key['name'])) {
			header('HTTP/1.0 400 Bad Request');
			die('You must specify a name for the key');
		}
	}

	function create_token() {
		// If libsodium is available use it :)
		if(true === function_exists('random_bytes')) {
			return bin2hex(random_bytes(16));
		} else {
			return sprintf('%04X%04X%04X%04X%04X%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
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
				$sql = 'SELECT id, name, token FROM api_keys WHERE id = :id';
				$query = $connection->prepare($sql);
				$query->bindValue(':id', $_GET['id']);
				if($query->execute()) {
					if($api_key = $query->fetch(\PDO::FETCH_ASSOC)) {
						echo json_encode($api_key);
						if(JSON_ERROR_NONE != json_last_error()) {
							header('HTTP/1.0 500 Internal Server Error');
							die(json_last_error_msg());
						}
					} else {
						header('HTTP/1.0 404 Not Found');
						die('We have no record of that api key');
					}
				} else {
					header('HTTP/1.0 500 Internal Server Error');
					//die($query->errorInfo()[2]);
					die('We experienced issues communicating with the database');
				}
			} else { // List
				$connection = DB::getConnection();
				$sql = 'SELECT id, name FROM api_keys';
				$query = $connection->prepare($sql);
				if($query->execute()) {
					$api_keys = $query->fetchAll(\PDO::FETCH_ASSOC);
					echo json_encode($api_keys);
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
				die('You must specify the api key to modify via the id param');
			}

			$api_key = json_decode(file_get_contents('php://input'), TRUE);
			if(NULL !== $api_key) {
				// validate api key
				validate($api_key);

				// okay to save to DB
				$connection = DB::getConnection();
				$sql = 'UPDATE api_keys SET name = :name WHERE id = :id';
				$query = $connection->prepare($sql);
				$query->bindValue(':id', $_GET['id']);
				$query->bindValue(':name', $api_key['name']);
				if($query->execute()) {
					// success
					// most drivers do not report the number of rows on an UPDATE
					// We'll just return the api key... but we'll update the value in the
					// id field for consistency
					$api_key['id'] = $_GET['id'];
					// make sure we report back the correct token
					$sql = 'SELECT token FROM api_keys WHERE id = :id';
					$query = $connection->prepare($sql);
					$query->bindValue(':id', $_GET['id']);
					if($query->execute()) {
						if($row = $query->fetch(\PDO::FETCH_NUM)) {
							$api_key['token'] = $row[0];
						}
					}
					echo json_encode($api_key);
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
			$api_key = json_decode(file_get_contents('php://input'), TRUE);
			if(NULL !== $api_key) {
				// validate api_key
				validate($api_key);
				$token = create_token();

				$connection = DB::getConnection();
				$sql = 'INSERT INTO api_keys(name, token) VALUES(:name, :token)';
				$query = $connection->prepare($sql);
				$query->bindValue(':name', $api_key['name']);
				$query->bindValue(':token', $token);
				if($query->execute()) {
					// success
					// most drivers do not report the number of rows on an INSERT
					// We'll return the api key after adding/overwriting an id field
					$api_key['id'] = $connection->lastInsertId('api_keys_id_seq');
					$api_key['token'] = $token;
					echo json_encode($api_key);
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
			if(!isset($_GET['id']) || empty($_GET['id'])) {
				header('HTTP/1.0 400 Bad Request');
				die('You must specify the api key to delete via the id param');
			}

			$connection = DB::getConnection();
			$sql = 'SELECT id, name, token FROM api_keys WHERE id = :id';
			$query = $connection->prepare($sql);
			$query->bindValue(':id', $_GET['id']);
			if($query->execute()) {
				if($api_key = $query->fetch(\PDO::FETCH_ASSOC)) {
					$sql = 'DELETE FROM api_keys WHERE id = :id';
					$query = $connection->prepare($sql);
					$query->bindValue(':id', $_GET['id']);
					if($query->execute()) {
						echo json_encode($api_key);
						if(JSON_ERROR_NONE != json_last_error()) {
							header('HTTP/1.0 500 Internal Server Error');
							die(json_last_error_msg());
						}
					}
				} else {
					header('HTTP/1.0 404 Not Found');
					die('We have no record of that api key');
				}
			} else {
				header('HTTP/1.0 500 Internal Server Error');
				//die($query->errorInfo()[2]);
				die('We experienced issues communicating with the database');
			}
			break;
		default:
			header('HTTP/1.0 405 Method Not Allowed');
			die('We were unable to understand your request.');
	}
	
?>