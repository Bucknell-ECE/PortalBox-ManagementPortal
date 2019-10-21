<?php
	/**
	 * validate - check that the parameter is an associative array with non empty
	 * values for the 'name' key and if all is well returns but if a check fails;
	 * the proper HTTP response is emitted and execution is halted.
	 */
	function validate($charge) {
		if(!is_array($charge)) {
			header('HTTP/1.0 500 Internal Server Error');
			die('We seem to have encountered an unexpected difficulty. Please ask your server administrator to investigate');
		}
		if(!array_key_exists('user_id', $charge) || empty($charge['user_id'])) {
			header('HTTP/1.0 400 Bad Request');
			die('You must specify the user_id for the charge');
		}
		if(!array_key_exists('equipment_id', $charge) || empty($charge['equipment_id'])) {
			header('HTTP/1.0 400 Bad Request');
			die('You must specify the equipment_id for the charge');
		}
		if(!array_key_exists('amount', $charge) || empty($charge['amount'])) {
			header('HTTP/1.0 400 Bad Request');
			die('You must specify the amount for the charge');
		}
	}

	// check authentication/authorization
	if((include_once '../lib/Security.php') === FALSE) {
		header('HTTP/1.0 500 Internal Server Error');
		die('We were unable to load some dependencies. Please ask your server administrator to investigate');
	}
	require_authorization('admin');

	// only authorized users should reach this point
	if((include_once '../lib/Database.php') === FALSE) {
		header('HTTP/1.0 500 Internal Server Error');
		die('We were unable to load some dependencies. Please ask your server administrator to investigate');
	}

	if((include_once '../lib/EncodeOutput.php') === FALSE) {
		header('HTTP/1.0 500 Internal Server Error');
		die('We were unable to load some dependencies. Please ask your server administrator to investigate');
	}

	// switch on the request method
	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET':		// List/Read
			if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
				$connection = DB::getConnection();
				$sql = 'SELECT c.id, c.user_id, u.name AS user, u.email, c.equipment_id, e.name AS equipment, c.time, c.amount, c.charge_policy_id, cp.name AS charge_policy, c.charge_rate, c.charged_time FROM charges AS c INNER JOIN charge_policies AS cp ON cp.id = c.charge_policy_id INNER JOIN equipment AS e ON e.id = c.equipment_id INNER JOIN users AS u on u.id = c.user_id WHERE c.id = :id';
				$query = $connection->prepare($sql);
				$query->bindValue(':id', $_GET['id']);
				if($query->execute()) {
					if($card = $query->fetch(\PDO::FETCH_ASSOC)) {
						render_json($card);
					} else {
						header('HTTP/1.0 404 Not Found');
						die('We have no record of that charge');
					}
				} else {
					header('HTTP/1.0 500 Internal Server Error');
					//die($query->errorInfo()[2]);
					die('We experienced issues communicating with the database');
				}
			} else { // List
				$connection = DB::getConnection();
				$sql = 'SELECT c.id, c.user_id, u.name AS user, u.email, c.equipment_id, e.name AS equipment, c.time, c.amount, c.charge_policy_id, cp.name AS charge_policy, c.charge_rate, c.charged_time FROM charges AS c INNER JOIN charge_policies AS cp ON cp.id = c.charge_policy_id INNER JOIN equipment AS e ON e.id = c.equipment_id INNER JOIN users AS u on u.id = c.user_id';

				$where_clause_elements = array();
				$parameters = array();
				if(isset($_GET['equipment_id']) && !empty($_GET['equipment_id'])) {
					$where_clause_elements[] = 'c.equipment_id = :equipment_id';
					$parameters[':equipment_id'] = $_GET['equipment_id'];
				}
				if(isset($_GET['user_id']) && !empty($_GET['user_id'])) {
					$where_clause_elements[] = 'c.user_id = :user_id';
					$parameters[':user_id'] = $_GET['user_id'];
				}
				if(isset($_GET['after']) && !empty($_GET['after'])) {
					$where_clause_elements[] = 'c.time >= :after';
					$parameters[':after'] = $_GET['after'];
				}
				if(isset($_GET['before']) && !empty($_GET['before'])) {
					$where_clause_elements[] = 'c.time <= :before';
					$parameters[':before'] = $_GET['before'];
				}
				if(0 < count($where_clause_elements)) {
					$sql .= ' WHERE ' . join(' AND ', $where_clause_elements);
				}
				$sql .= ' ORDER BY c.time DESC';

				$query = $connection->prepare($sql);
				foreach($parameters as $k => $v) {
					$query->bindValue($k, $v);
				}
				if($query->execute()) {
					$charges = $query->fetchAll(\PDO::FETCH_ASSOC);
					render_json($charges);
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
				die('You must specify the charge to modify via the id param');
			}

			$charge = json_decode(file_get_contents('php://input'), TRUE);
			if(NULL !== $charge) {
				validate($charge);

				$connection = DB::getConnection();
				$sql = 'UPDATE charges SET amount = :amount, charge_policy_id = 1 WHERE id = :id';
				$query = $connection->prepare($sql);
				$query->bindValue(':id', $_GET['id']);
				$query->bindValue(':amount', $charge['amount']);
				if($query->execute()) {
					// success
					// most drivers do not report the number of rows on an UPDATE
					// We'll just return the equipment... but we'll update the value in the
					// id field for consistency
					$charge['id'] = $_GET['id'];
					render_json($charge);
				} else {
					header('HTTP/1.0 500 Internal Server Error');
					die($query->errorInfo()[2]);
					die('We experienced issues communicating with the database');
				}
			} else {
				header('HTTP/1.0 400 Bad Request');
				die(json_last_error_msg());
			}
			break;
		case 'PUT':		// Create
			// intentional fall through, creation not allowed
		case 'DELETE':	// Delete
			// intentional fall through, deletion not allowed, but maybe it should be?
		default:
			header('HTTP/1.0 405 Method Not Allowed');
			die('We were unable to understand your request.');
	}
	
?>