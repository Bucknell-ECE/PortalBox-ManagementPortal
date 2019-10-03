<?php
	/**
	 * validate - check that the parameter is an associative array with non empty
	 * values for the 'name' key and if all is well returns but if a check fails;
	 * the proper HTTP response is emitted and execution is halted.
	 */
	function validate($payment) {
		if(!is_array($payment)) {
			header('HTTP/1.0 500 Internal Server Error');
			die('We seem to have encountered an unexpected difficulty. Please ask your server administrator to investigate');
		}
		if(!array_key_exists('user_id', $payment) || empty($payment['user_id'])) {
			header('HTTP/1.0 400 Bad Request');
			die('You must specify the user for the payment');
		}
		if(!array_key_exists('amount', $payment) || empty($payment['amount'])) {
			header('HTTP/1.0 400 Bad Request');
			die('You must specify the amount for the payment');
		}
		if(!array_key_exists('time', $payment) || empty($payment['time'])) {
			header('HTTP/1.0 400 Bad Request');
			die('You must specify the time for the payment');
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
		die('We were unable to load some dependencies. Please ask your server administrator to investigate');
	}

	// switch on the request method
	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET':		// List/Read
			if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
				$connection = DB::getConnection();
				$sql = 'SELECT p.id, p.user_id, u.name AS user, u.email, p.amount, p.time FROM payments AS p INNER JOIN users AS u on u.id = p.user_id WHERE p.id = :id';
				$query = $connection->prepare($sql);
				$query->bindValue(':id', $_GET['id']);
				if($query->execute()) {
					if($payment = $query->fetch(\PDO::FETCH_ASSOC)) {
						echo json_encode($payment);
					} else {
						header('HTTP/1.0 404 Not Found');
						die('We have no record of that payment');
					}
				} else {
					header('HTTP/1.0 500 Internal Server Error');
					//die($query->errorInfo()[2]);
					die('We experienced issues communicating with the database');
				}
			} else { // List
				$connection = DB::getConnection();
				$sql = 'SELECT p.id, p.user_id, u.name AS user, u.email, p.amount, p.time FROM payments AS p INNER JOIN users AS u on u.id = p.user_id';

				$where_clause_elements = array();
				$parameters = array();
				if(isset($_GET['user_id']) && !empty($_GET['user_id'])) {
					$where_clause_elements[] = 'p.user_id = :user_id';
					$parameters[':user_id'] = $_GET['user_id'];
				}
				if(isset($_GET['after']) && !empty($_GET['after'])) {
					$where_clause_elements[] = 'p.time >= :after';
					$parameters[':after'] = $_GET['after'];
				}
				if(isset($_GET['before']) && !empty($_GET['before'])) {
					$where_clause_elements[] = 'p.time <= :before';
					$parameters[':before'] = $_GET['before'];
				}
				if(0 < count($where_clause_elements)) {
					$sql .= ' WHERE ' . join(' AND ', $where_clause_elements);
				}
				$sql .= ' ORDER BY p.time DESC';

				$query = $connection->prepare($sql);
				foreach($parameters as $k => $v) {
					$query->bindValue($k, $v);
				}
				if($query->execute()) {
					$payments = $query->fetchAll(\PDO::FETCH_ASSOC);
					echo json_encode($payments);
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
				die('You must specify the payment to modify via the id param');
			}

			$payment = json_decode(file_get_contents('php://input'), TRUE);
			if(NULL !== $payment) {
				validate($payment);

				$connection = DB::getConnection();
				$sql = 'UPDATE payments SET amount = :amount, time = :time, user_id = :user_id WHERE id = :id';
				$query = $connection->prepare($sql);
				$query->bindValue(':id', $_GET['id']);
				$query->bindValue(':amount', $payment['amount']);
				$query->bindValue(':time', $payment['time']);
				$query->bindValue(':user_id', $payment['user_id']);
				if($query->execute()) {
					// success
					// most drivers do not report the number of rows on an UPDATE
					// We'll just return the equipment... but we'll update the value in the
					// id field for consistency
					$payment['id'] = $_GET['id'];
					echo json_encode($payment);
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
			$payment = json_decode(file_get_contents('php://input'), TRUE);
			if(NULL !== $payment) {
				validate($payment);

				$connection = DB::getConnection();
				$sql = 'INSERT INTO payments(amount, time, user_id) VALUES(:amount, :time, :user_id)';
				$query = $connection->prepare($sql);
				$query->bindValue(':amount', $payment['amount']);
				$query->bindValue(':time', $payment['time']);
				$query->bindValue(':user_id', $payment['user_id']);
				if($query->execute()) {
					// success
					// most drivers do not report the number of rows on an INSERT
					// We'll return the location after adding/overwriting an id field
					$payment['id'] = $connection->lastInsertId('payments_id_seq');
					echo json_encode($payment);
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
			// intentional fall through, deletion not allowed, but maybe it should be?
		default:
			header('HTTP/1.0 405 Method Not Allowed');
			die('We were unable to understand your request.');
	}
	
?>