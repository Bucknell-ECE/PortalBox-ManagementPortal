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
		die('We were unable to load some dependencies. Please ask your server administrator to investigate');
	}

	// switch on the request method
	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET':		// List
			// Build our Query
			$sql = 'SELECT l.id, l.time, et.name AS event_type, l.equipment_id, e.name AS equipment, l.card_id, u.name AS user FROM log AS l INNER JOIN event_types AS et ON et.id = l.event_type_id INNER JOIN equipment AS e ON l.equipment_id = e.id LEFT JOIN users_x_cards AS uxc ON l.card_id = uxc.card_id LEFT JOIN users AS u on u.id = uxc.user_id';

			$where_clause_elements = array();
			$parameters = array();
			if(isset($_GET['equipment_id']) && !empty($_GET['equipment_id'])) {
				$where_clause_elements[] = 'l.equipment_id = :equipment_id';
				$parameters[':equipment_id'] = $_GET['equipment_id'];
			}
			if(isset($_GET['location_id']) && !empty($_GET['location_id'])) {
				$where_clause_elements[] = 'e.location_id = :location_id';
				$parameters[':location_id'] = $_GET['location_id'];
			}
			if(isset($_GET['after']) && !empty($_GET['after'])) {
				$where_clause_elements[] = 'l.time >= :after';
				$parameters[':after'] = $_GET['after'];
			}
			if(isset($_GET['before']) && !empty($_GET['before'])) {
				$where_clause_elements[] = 'l.time <= :before';
				$parameters[':before'] = $_GET['before'];
			}
			if(0 < count($where_clause_elements)) {
				$sql .= ' WHERE ' . join(' AND ', $where_clause_elements);
			} else {
				header('HTTP/1.0 400 Bad Request');
				die('Our unfiltered logs can be very large. We therefore require API users to limit their log requests in some way');
			}
			$sql .= ' ORDER BY l.time DESC';
			$connection = DB::getConnection();
			$query = $connection->prepare($sql);

			// run search
			foreach($parameters as $k => $v) {
				$query->bindValue($k, $v);
			}
			if($query->execute()) {
				$events = $query->fetchAll(PDO::FETCH_ASSOC);
				echo json_encode($events);
			} else {
				header('HTTP/1.0 500 Internal Server Error');
				//die($query->errorInfo()[2]);
				die('We experienced issues communicating with the database');
			}
			break;
		case 'POST':	// Update
			// intentional fall through, users should not modify log entries
		case 'PUT':		// Create
			// intentional fall through, users should not create log entries
		case 'DELETE':	// Delete
			// intentional fall through, deletion not allowed
		default:
			header('HTTP/1.0 405 Method Not Allowed');
			die('We were unable to understand your request.');
	}
	
?>