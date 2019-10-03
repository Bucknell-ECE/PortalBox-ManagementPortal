<?php
	/**
	 * validate check that the paramter is an associative array with non empty
	 * values for the 'name', 'type_id', 'mac_address', and 'location_id'
	 * keys and the presence of a timeout key then if all is well returns but if
	 * a check fails; the proper HTTP response is emitted and execution is halted.
	 */
	function validate($equipment) {
		if(!is_array($equipment)) {
			header('HTTP/1.0 500 Internal Server Error');
			die('We seem to have encountered an unexpected difficulty. Please ask your server administrator to investigate');
		}
		if(!array_key_exists('name', $equipment) || empty($equipment['name'])) {
			header('HTTP/1.0 400 Bad Request');
			die('You must specify the equipment\'s name');
		}
		if(!array_key_exists('type_id', $equipment) || empty($equipment['type_id'])) {
			header('HTTP/1.0 400 Bad Request');
			die('You must specify the equipment\'s type_id');
		} else {
			$connection = DB::getConnection();
			$sql = 'SELECT id FROM equipment_types WHERE id = :id';
			$query = $connection->prepare($sql);
			$query->bindValue(':id', $equipment['type_id']);
			if($query->execute()) {
				$type = $query->fetch(PDO::FETCH_ASSOC);
				if(!$type) {
					header('HTTP/1.0 400 Bad Request');
					die('You must specify a valid type_id for the equipment');
				}
			} else {
				header('HTTP/1.0 500 Internal Server Error');
				die('We experienced issues communicating with the database');
			}
		}
		if(!array_key_exists('mac_address', $equipment) || empty($equipment['mac_address'])) {
			header('HTTP/1.0 400 Bad Request');
			die('You must specify the equipment\'s mac_address');
		} else if(FALSE == preg_match('/^([0-9A-Fa-f]{2}[:-]?){5}([0-9A-Fa-f]{2})$/', $equipment['mac_address'])) {
			header('HTTP/1.0 400 Bad Request');
			die('You must specify a valid mac_address for the equipment eg. 00:11:22:AA:BB:CC');
		}
		if(!array_key_exists('location_id', $equipment) || empty($equipment['location_id'])) {
			header('HTTP/1.0 400 Bad Request');
			die('You must specify the equipment\'s location_id');
		} else {
			$connection = DB::getConnection();
			$sql = 'SELECT id FROM locations WHERE id = :id';
			$query = $connection->prepare($sql);
			$query->bindValue(':id', $equipment['location_id']);
			if($query->execute()) {
				$location = $query->fetch(PDO::FETCH_ASSOC);
				if(!$location) {
					header('HTTP/1.0 400 Bad Request');
					die('You must specify a valid location_id for the equipment');
				}
			} else {
				header('HTTP/1.0 500 Internal Server Error');
				die('We experienced issues communicating with the database');
			}
		}
		if(!array_key_exists('timeout', $equipment) || 0 > intval($equipment['timeout'])) {
			header('HTTP/1.0 400 Bad Request');
			die('You must specify the equipment\'s timeout');
		}
	}

	// all users can use this endpoint check authentication in each method

	if((include_once '../lib/Database.php') === FALSE) {
		header('HTTP/1.0 500 Internal Server Error');
		die('We were unable to load some dependencies. Please ask your server administrator to investigate');
	}

	// switch on the request method
	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET':		// List/Read
			if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
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
					die('You have not been granted permissions to view full equipment details.');
				}

				$connection = DB::getConnection();
				$sql = 'SELECT e.id, e.name, e.type_id, t.name AS type, e.mac_address, e.location_id, l.name AS location, e.timeout, iu.equipment_id IS NOT NULL AS in_use FROM equipment AS e JOIN equipment_types AS t ON e.type_id = t.id JOIN locations AS l ON e.location_id = l.id LEFT JOIN in_use AS iu ON e.id = iu.equipment_id WHERE e.id = :id';
				$query = $connection->prepare($sql);
				$query->bindValue(':id', $_GET['id']);
				if($query->execute()) {
					if($equipment = $query->fetch(\PDO::FETCH_ASSOC)) {
						if($equipment['in_use']) {
							$equipment['in_use'] = true;
						} else {
							$equipment['in_use'] = false;
						}
						// join in cards
						echo json_encode($equipment);
					} else {
						header('HTTP/1.0 404 Not Found');
						die('We have no record of that equipment');
					}
				} else {
					header('HTTP/1.0 500 Internal Server Error');
					//die($query->errorInfo()[2]);
					die('We experienced issues communicating with the database');
				}
			} else { // List
				// check authentication
				$authenticated = false;
				if(session_status() !== PHP_SESSION_ACTIVE) {
					$success = session_start();
					if(!$success) {
						session_abort();
					} else {
						if(array_key_exists('user', $_SESSION)) {
							$authenticated = true;
						}
					}
				}
				$connection = DB::getConnection();
				$sql = '';
				if($authenticated) {
					$sql = 'SELECT e.id, e.name, e.type_id, t.name AS type, e.mac_address, e.location_id, l.name AS location, e.timeout, iu.equipment_id IS NOT NULL AS in_use FROM equipment AS e JOIN equipment_types AS t ON e.type_id = t.id JOIN locations AS l ON e.location_id = l.id LEFT JOIN in_use AS iu ON e.id = iu.equipment_id';
				} else {
					$sql = 'SELECT e.id, e.name, t.name AS type, l.name AS location, iu.equipment_id IS NOT NULL AS in_use FROM equipment AS e JOIN equipment_types AS t ON e.type_id = t.id JOIN locations AS l ON e.location_id = l.id LEFT JOIN in_use AS iu ON e.id = iu.equipment_id ORDER BY l.name';
				}
				$query = $connection->prepare($sql);
				if($query->execute()) {
					$equipment = $query->fetchAll(\PDO::FETCH_ASSOC);
					foreach($equipment as &$e) {
						if($e['in_use']) {
							$e['in_use'] = true;
						} else {
							$e['in_use'] = false;
						}
					}
					unset($e);
					echo json_encode($equipment);
				} else {
					header('HTTP/1.0 500 Internal Server Error');
					die($query->errorInfo()[2]);
					//die('We experienced issues communicating with the database');
				}
			}
			break;
		case 'POST':	// Update
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
				die('You have not been granted permissions to modify equipment.');
			}
			// validate that we have an oid
			if(!isset($_GET['id']) || empty($_GET['id'])) {
				header('HTTP/1.0 400 Bad Request');
				die('You must specify the equipment to modify via the id param');
			}

			$equipment = json_decode(file_get_contents('php://input'), TRUE);
			if(NULL !== $equipment) {
				// validate equipment
				validate($equipment);
				$connection = DB::getConnection();
				$sql = 'SELECT id FROM equipment WHERE mac_address = :mac_address';
				$query = $connection->prepare($sql);
				$query->bindValue(':mac_address', $equipment['mac_address']);
				if($query->execute()) {
					$existing = $query->fetch(PDO::FETCH_ASSOC);
					if($existing && $existing['id'] != $_GET['id']) {
						header('HTTP/1.0 400 Bad Request');
						die('You must specify a mac_address not already in use by other equipment for the equipment');
					}
				} else {
					header('HTTP/1.0 500 Internal Server Error');
					die('We experienced issues communicating with the database');
				}

				// Save to the database
				$sql = 'UPDATE equipment SET name = :name, type_id = :type_id, mac_address = :mac_address, location_id = :location_id, timeout = :timeout WHERE id = :id';
				$query = $connection->prepare($sql);
				$query->bindValue(':id', $_GET['id']);
				$query->bindValue(':name', $equipment['name']);
				$query->bindValue(':type_id', $equipment['type_id']);
				$query->bindValue(':mac_address', str_replace(array('-', ':'), '', $equipment['mac_address']));
				$query->bindValue(':location_id', $equipment['location_id']);
				$query->bindValue(':timeout', $equipment['timeout'], PDO::PARAM_INT);
				if($query->execute()) {
					// success
					// most drivers do not report the number of rows on an UPDATE
					// We'll just return the equipment... but we'll update the value in the
					// id field for consistency
					$equipment['id'] = $_GET['id'];
					echo json_encode($equipment);
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
				die('You have not been granted permission to add equipment to this system.');
			}

			$equipment = json_decode(file_get_contents('php://input'), TRUE);
			if(NULL !== $equipment) {
				// validate equipment
				validate($equipment);
				$connection = DB::getConnection();
				$sql = 'SELECT id FROM equipment WHERE mac_address = :mac_address';
				$query = $connection->prepare($sql);
				$query->bindValue(':mac_address', $equipment['mac_address']);
				if($query->execute()) {
					$existing = $query->fetch(PDO::FETCH_ASSOC);
					if($existing) {
						header('HTTP/1.0 400 Bad Request');
						die('You must specify a mac_address not already in use for the equipment');
					}
				} else {
					header('HTTP/1.0 500 Internal Server Error');
					die('We experienced issues communicating with the database');
				}

				// Save to the database
				$sql = 'INSERT INTO equipment(name, type_id, mac_address, location_id, timeout) VALUES(:name, :type_id, :mac_address, :location_id, :timeout)';
				$query = $connection->prepare($sql);
				$query->bindValue(':name', $equipment['name']);
				$query->bindValue(':type_id', $equipment['type_id']);
				$query->bindValue(':mac_address', str_replace(array('-', ':'), '', $equipment['mac_address']));
				$query->bindValue(':location_id', $equipment['location_id']);
				$query->bindValue(':timeout', $equipment['timeout'], PDO::PARAM_INT);
				if($query->execute()) {
					// success
					// most drivers do not report the number of rows on an INSERT
					// We'll return the equipment after adding/overwriting an id field
					$equipment['id'] = $connection->lastInsertId('equipment_id_seq');
					echo json_encode($equipment);
				} else {
					header('HTTP/1.0 500 Internal Server Error');
					die($query->errorInfo()[2]);
					//die('We experienced issues communicating with the database');
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