<?php
	/**
	 * validate - check that the parameter is an associative array with non empty
	 * values for the 'name' key and if all is well returns but if a check fails;
	 * the proper HTTP response is emitted and execution is halted.
	 */
	function validate($equipment_type) {
		if(!is_array($equipment_type)) {
			header('HTTP/1.0 500 Internal Server Error');
			die('We seem to have encountered an unexpected difficulty. Please ask your server administrator to investigate');
		}
		if(!array_key_exists('name', $equipment_type) || empty($equipment_type['name'])) {
			header('HTTP/1.0 400 Bad Request');
			die('You must specify the equipment type\'s name');
		}
		if(!array_key_exists('requires_training', $equipment_type) || !is_bool($equipment_type['requires_training'])) {
			header('HTTP/1.0 400 Bad Request');
			die('You must specify whether the equipment type requires training');
		}
		if(!array_key_exists('charge_policy_id', $equipment_type)) {
			header('HTTP/1.0 400 Bad Request');
			die('You must specify whether the equipment type requires payment');
		} else {
			if(1 >= intval($equipment_type['charge_policy_id']) || 5 <= intval($equipment_type['charge_policy_id'])) {
				header('HTTP/1.0 400 Bad Request');
				die('You must specify a valid charge_policy_id for the equipment');
			}
		}
		if(2 != intval($equipment_type['charge_policy_id'])) {
			if(!array_key_exists('charge_rate', $equipment_type)) {
				header('HTTP/1.0 400 Bad Request');
				die('You must specify the rate that usage will be charged for the equipment type');
			}
		}
	}

	// check authentication
	// trainers and admins can use this endpoint, we'll have to check authorization in each method
	if((include_once '../lib/Security.php') === FALSE) {
		header('HTTP/1.0 500 Internal Server Error');
		die('We were unable to load some dependencies. Please ask your server administrator to investigate');
	}
	require_authentication();

	// only authenticated users should reach this point
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
			require_authorization('trainer');
			if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
				$connection = DB::getConnection();
				$sql = 'SELECT et.id, et.name, et.requires_training, et.charge_policy_id, cp.name AS charge_policy, et.charge_rate FROM equipment_types AS et INNER JOIN charge_policies AS cp ON cp.id = et.charge_policy_id WHERE et.id = :id';
				$query = $connection->prepare($sql);
				$query->bindValue(':id', $_GET['id']);
				if($query->execute()) {
					if($equipment_type = $query->fetch(\PDO::FETCH_ASSOC)) {
						if($equipment_type['requires_training']) {
							$equipment_type['requires_training'] = true;
						} else {
							$equipment_type['requires_training'] = false;
						}
						render_json($equipment_type);
					} else {
						header('HTTP/1.0 404 Not Found');
						die('We have no record of that equipment type');
					}
				} else {
					header('HTTP/1.0 500 Internal Server Error');
					//die($query->errorInfo()[2]);
					die('We experienced issues communicating with the database');
				}
			} else { // List
				$connection = DB::getConnection();
				$sql = 'SELECT et.id, et.name, et.requires_training, et.charge_policy_id, cp.name AS charge_policy, et.charge_rate FROM equipment_types AS et INNER JOIN charge_policies AS cp ON cp.id = et.charge_policy_id';
				$query = $connection->prepare($sql);
				if($query->execute()) {
					$equipment_types = $query->fetchAll(\PDO::FETCH_ASSOC);
					foreach($equipment_types as &$equipment_type) {
						if($equipment_type['requires_training']) {
							$equipment_type['requires_training'] = true;
						} else {
							$equipment_type['requires_training'] = false;
						}
					}
					unset($equipment_type);
					render_json($equipment_types);
				} else {
					header('HTTP/1.0 500 Internal Server Error');
					//die($query->errorInfo()[2]);
					die('We experienced issues communicating with the database');
				}
			}
			break;
		case 'POST':	// Update
			require_authorization('admin');

			// validate that we have an oid
			if(!isset($_GET['id']) || empty($_GET['id'])) {
				header('HTTP/1.0 400 Bad Request');
				die('You must specify the equipment type to modify via the id param');
			}

			$equipment_type = json_decode(file_get_contents('php://input'), TRUE);
			if(NULL !== $equipment_type) {
				// validate equipment_type
				validate($equipment_type);

				// okay to save to DB
				$connection = DB::getConnection();
				$sql = 'UPDATE equipment_types SET name = :name, requires_training = :requires_training, charge_policy_id = :charge_policy_id, charge_rate = :charge_rate WHERE id = :id';
				$query = $connection->prepare($sql);
				$query->bindValue(':id', $_GET['id']);
				$query->bindValue(':name', $equipment_type['name']);
				$query->bindValue(':requires_training', $equipment_type['requires_training']);
				$query->bindValue(':charge_policy_id', $equipment_type['charge_policy_id']);
				if(2 == intval($equipment_type['charge_policy_id'])) {
					$query->bindValue(':charge_rate', NULL, PDO::PARAM_NULL);
					$equipment_type['charge_rate'] = NULL;
				} else {
					$query->bindValue(':charge_rate', $equipment_type['charge_rate']);
				}
				
				if($query->execute()) {
					// success
					// most drivers do not report the number of rows on an UPDATE
					// We'll just return the equipment_type... but we'll update the
					// value in the id field for consistency
					$equipment_type['id'] = $_GET['id'];
					render_json($equipment_type);
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
		case 'PUT':	// Create
			require_authorization('admin');

			$equipment_type = json_decode(file_get_contents('php://input'), TRUE);
			if(NULL !== $equipment_type) {
				// validate equipment_type
				validate($equipment_type);

				$connection = DB::getConnection();
				$sql = 'INSERT INTO equipment_types(name, requires_training, charge_policy_id, charge_rate) VALUES(:name, :requires_training, :charge_policy_id, :charge_rate)';
				$query = $connection->prepare($sql);
				$query->bindValue(':name', $equipment_type['name']);
				$query->bindValue(':requires_training', $equipment_type['requires_training']);
				$query->bindValue(':charge_policy_id', $equipment_type['charge_policy_id']);
				if(2 == intval($equipment_type['charge_policy_id'])) {
					$query->bindValue(':charge_rate', NULL, PDO::PARAM_NULL);
					$equipment_type['charge_rate'] = NULL; 
				} else {
					$query->bindValue(':charge_rate', $equipment_type['charge_rate']);
				}
				if($query->execute()) {
					// success
					// most drivers do not report the number of rows on an INSERT
					// We'll return the equipment_type after adding/overwriting an id field
					$equipment_type['id'] = $connection->lastInsertId('equipment_types_id_seq');
					render_json($equipment_type);
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