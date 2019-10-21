<?php
	/**
	 * validate - check that the parameter is an associative array with non empty
	 * values for the 'name' key and if all is well returns but if a check fails;
	 * the proper HTTP response is emitted and execution is halted.
	 */
	function validate($card) {
		if(!is_array($card)) {
			header('HTTP/1.0 500 Internal Server Error');
			die('We seem to have encountered an unexpected difficulty. Please ask your server administrator to investigate');
		}
		if(!array_key_exists('id', $card) || empty($card['id'])) {
			header('HTTP/1.0 400 Bad Request');
			die('You must specify the card id');
		}
		if(!array_key_exists('type_id', $card) || empty($card['type_id'])) {
			header('HTTP/1.0 400 Bad Request');
			die('You must specify the card type_id');
		}
		// Warning hard coded values! Of course it would be exceedingly hard to
		// change these values as they determine the behavior of the Portal Box
		// Application
		if(3 == $card['type_id']) {
			if(!array_key_exists('equipment_type_id', $card) || empty($card['equipment_type_id'])) {
				header('HTTP/1.0 400 Bad Request');
				die('For a training card, you must specify the card equipment_type_id');
			}
		}
		if(4 == $card['type_id']) {
			if(!array_key_exists('user_id', $card) || empty($card['user_id'])) {
				header('HTTP/1.0 400 Bad Request');
				die('For a user card, you must specify the card user_id');
			}
		}
	}

	// check authentication/authorization
	if((include_once '../lib/Security.php') === FALSE) {
		header('HTTP/1.0 500 Internal Server Error');
		die('We were unable to load some dependencies. Please ask your server administrator to investigate');
	}
	require_authorization('trainer');
	$access_level = get_user_authorization_level();

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
			if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
				$connection = DB::getConnection();
				$sql = 'SELECT c.id, c.type_id, t.name AS type, uxc.user_id, u.name AS user, etxc.equipment_type_id, et.name AS equipment_type FROM cards AS c INNER JOIN card_types AS t ON c.type_id = t.id LEFT JOIN users_x_cards AS uxc ON c.id = uxc.card_id LEFT JOIN users AS u on u.id = uxc.user_id LEFT JOIN equipment_type_x_cards AS etxc ON c.id = etxc.card_id LEFT JOIN equipment_types AS et ON et.id = etxc.equipment_type_id WHERE c.id = :id';
				$query = $connection->prepare($sql);
				$query->bindValue(':id', $_GET['id']);
				if($query->execute()) {
					if($card = $query->fetch(\PDO::FETCH_ASSOC)) {
						render_json($card);
					} else {
						header('HTTP/1.0 404 Not Found');
						die('We have no record of that card');
					}
				} else {
					header('HTTP/1.0 500 Internal Server Error');
					//die($query->errorInfo()[2]);
					die('We experienced issues communicating with the database');
				}
			} else { // List
				$connection = DB::getConnection();
				if(2 < $access_level) { // admin
					$sql = 'SELECT c.id, c.type_id, t.name AS type FROM cards AS c JOIN card_types AS t ON c.type_id = t.id';
				} else { // not admin but trainer required :. trainer
					// WARNING!!! Hardcoded value card type 4 is user cards
					$sql = 'SELECT c.id, c.type_id, t.name AS type FROM cards AS c JOIN card_types AS t ON c.type_id = t.id WHERE c.type_id = 4';
				}

				// can not search because c.id is a BIGINT
//				if(isset($_GET['search']) && !empty($_GET['search'])) {
//					$sql .= ' WHERE c.id LIKE :pattern';
//				}
				$query = $connection->prepare($sql);
//				if(isset($_GET['search']) && !empty($_GET['search'])) {
//					$query->bindValue(':pattern', '%' . urldecode($_GET['search']) . '%');
//				}

				if($query->execute()) {
					$cards = $query->fetchAll(\PDO::FETCH_ASSOC);
					render_json($cards);
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
				die('You must specify the card to modify via the id param');
			}

			$card = json_decode(file_get_contents('php://input'), TRUE);
			if(NULL !== $card) {
				// validate card
				validate($card);

				// okay to save to DB... we need to know the current pre change
				// type inorder to consistently update FKs
				$connection = DB::getConnection();
				$sql = 'SELECT type_id FROM cards WHERE id = :id';
				$query = $connection->prepare($sql);
				$query->bindValue(':id', $_GET['id']);
				$connection->beginTransaction(); // just a select but we could have a race condition otherwise
				if($query->execute()) {
					if($original_card = $query->fetch(\PDO::FETCH_ASSOC)) {
						$sql = 'UPDATE cards SET id = :new_card_id, type_id = :type_id WHERE id = :current_card_id';
						$query = $connection->prepare($sql);
						$query->bindValue(':current_card_id', $_GET['id']);
						$query->bindValue(':new_card_id', $card['id']);
						$query->bindValue(':type_id', $card['type_id']);
						if($query->execute()) {
							// we have linked data to perhaps update
							// type could have changed and we need to delete old FKs then create new
							// type could be the same but link changed
							if($original_card['type_id'] != $card['type_id']) {
								// remove old links
								if(3 == $original_card['type_id']) {
									$sql = 'DELETE FROM equipment_type_x_cards WHERE card_id = :id';
									$query = $connection->prepare($sql);
									$query->bindValue(':id', $card['id']);
									if(!$query->execute()) {
										$connection->rollBack();
										header('HTTP/1.0 500 Internal Server Error');
										//die($query->errorInfo()[2]);
										die('We experienced issues communicating with the database');
									}
								} elseif(4 == $original_card['type_id']) {
									$sql = 'DELETE FROM user_x_cards WHERE card_id = :id';
									$query = $connection->prepare($sql);
									$query->bindValue(':id', $card['id']);
									if(!$query->execute()) {
										$connection->rollBack();
										header('HTTP/1.0 500 Internal Server Error');
										//die($query->errorInfo()[2]);
										die('We experienced issues communicating with the database');
									}
								}

								// add new links
								if(3 == $card['type_id']) {
									$sql = 'INSERT INTO equipment_type_x_cards(equipment_type_id, card_id) VALUES (:equipment_type_id, :id)';
									$query = $connection->prepare($sql);
									$query->bindValue(':id', $card['id']);
									$query->bindValue(':equipment_type_id', $card['equipment_type_id']);
									if(!$query->execute()) {
										$connection->rollBack();
										header('HTTP/1.0 500 Internal Server Error');
										//die($query->errorInfo()[2]);
										die('We experienced issues communicating with the database');
									}
								} elseif(4 == $card['type_id']) {
									$sql = 'INSERT INTO users_x_cards(user_id, card_id) VALUES (:user_id, :id)';
									$query = $connection->prepare($sql);
									$query->bindValue(':id', $card['id']);
									$query->bindValue(':user_id', $card['user_id']);
									if(!$query->execute()) {
										$connection->rollBack();
										header('HTTP/1.0 500 Internal Server Error');
										//die($query->errorInfo()[2]);
										die('We experienced issues communicating with the database');
									}
								}
							} else {
								// update existing links
								if(3 == $card['type_id']) {
									$sql = 'UPDATE equipment_type_x_cards SET equipment_type_id = :equipment_type_id WHERE card_id = :id';
									$query = $connection->prepare($sql);
									$query->bindValue(':id', $card['id']);
									$query->bindValue(':equipment_type_id', $card['equipment_type_id']);
									if(!$query->execute()) {
										$connection->rollBack();
										header('HTTP/1.0 500 Internal Server Error');
										//die($query->errorInfo()[2]);
										die('We experienced issues communicating with the database');
									}
								} elseif(4 == $card['type_id']) {
									$sql = 'UPDATE users_x_cards SET user_id = :user_id WHERE card_id = :id';
									$query = $connection->prepare($sql);
									$query->bindValue(':id', $card['id']);
									$query->bindValue(':user_id', $card['user_id']);
									if(!$query->execute()) {
										$connection->rollBack();
										header('HTTP/1.0 500 Internal Server Error');
										//die($query->errorInfo()[2]);
										die('We experienced issues communicating with the database');
									}
								}
							}

							$connection->commit();
							render_json($card);
						} else {
							$connection->rollBack();
							header('HTTP/1.0 500 Internal Server Error');
							//die($query->errorInfo()[2]);
							die('We experienced issues communicating with the database');
						}
					} else {
						$connection->rollBack();
						header('HTTP/1.0 404 Not Found');
						die('We have no record of that card');
					}
				} else {
					$connection->rollBack();
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
			$card = json_decode(file_get_contents('php://input'), TRUE);
			if(NULL !== $card) {
				// validate card
				validate($card);

				$connection = DB::getConnection();

				if(2 < $access_level) { // admin
					$sql = 'INSERT INTO cards VALUES(:id, :type_id)';
					$query = $connection->prepare($sql);
					$query->bindValue(':id', $card['id']);
					$query->bindValue(':type_id', $card['type_id']);
					$connection->beginTransaction();
					if($query->execute()) {
						// most drivers do not report the number of rows on an INSERT
						// since we explicitly set the oid we don't even need to look at lastInsertId
						if(3 == $card['type_id']) {
							$sql = 'INSERT INTO equipment_type_x_cards(equipment_type_id, card_id) VALUES (:equipment_type_id, :id)';
							$query = $connection->prepare($sql);
							$query->bindValue(':id', $card['id']);
							$query->bindValue(':equipment_type_id', $card['equipment_type_id']);
							if(!$query->execute()) {
								$connection->rollBack();
								header('HTTP/1.0 500 Internal Server Error');
								//die($query->errorInfo()[2]);
								die('We experienced issues communicating with the database');
							}
						} elseif(4 == $card['type_id']) {
							$sql = 'INSERT INTO users_x_cards(user_id, card_id) VALUES (:user_id, :id)';
							$query = $connection->prepare($sql);
							$query->bindValue(':id', $card['id']);
							$query->bindValue(':user_id', $card['user_id']);
							if(!$query->execute()) {
								$connection->rollBack();
								header('HTTP/1.0 500 Internal Server Error');
								//die($query->errorInfo()[2]);
								die('We experienced issues communicating with the database');
							}
						}

						$connection->commit();
						render_json($card);
					} else {
						$connection->rollBack();
						header('HTTP/1.0 500 Internal Server Error');
						//die($query->errorInfo()[2]);
						die('We experienced issues communicating with the database');
					}
				} else { // not admin but trainer required :. trainer
					// WARNING HARDCODED VALUE!!! 
					if(4 != $card['type_id']) {
						header('HTTP/1.0 403 Forbidden');
						//die($query->errorInfo()[2]);
						die('You have not been granted the privilege to create a card of the specified type');
					}

					$sql = 'INSERT INTO cards VALUES(:id, 4)';
					$query = $connection->prepare($sql);
					$query->bindValue(':id', $card['id']);
					$connection->beginTransaction();
					if($query->execute()) {
						// most drivers do not report the number of rows on an INSERT
						// since we explicitly set the oid we don't even need to look at lastInsertId
						
						$sql = 'INSERT INTO users_x_cards(user_id, card_id) VALUES (:user_id, :id)';
						$query = $connection->prepare($sql);
						$query->bindValue(':id', $card['id']);
						$query->bindValue(':user_id', $card['user_id']);
						if(!$query->execute()) {
							$connection->rollBack();
							header('HTTP/1.0 500 Internal Server Error');
							//die($query->errorInfo()[2]);
							die('We experienced issues communicating with the database');
						}

						$connection->commit();
						render_json($card);
					} else {
						$connection->rollBack();
						header('HTTP/1.0 500 Internal Server Error');
						//die($query->errorInfo()[2]);
						die('We experienced issues communicating with the database');
					}
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