<?php
	/**
	 * validate - check that the parameter is an associative array with non-
	 * empty values for the 'name' and 'email' keys. If all is well returns
	 * but if a check fails; the proper HTTP response is emitted and execution
	 * is halted.
	 */
	function validate($user) {
		if(!is_array($user)) {
			header('HTTP/1.0 500 Internal Server Error');
			die('We seem to have encountered an unexpected difficulty. Please ask you server administrator to investigate');
		}
		if(!array_key_exists('name', $user) || empty($user['name'])) {
			header('HTTP/1.0 400 Bad Request');
			die('You must specify the user\'s name');
		}
		if(!array_key_exists('email', $user) || empty($user['email'])) {
			header('HTTP/1.0 400 Bad Request');
			die('You must specify the user\'s email');
		}
		// warning hardcoded values... management access levels are baked in
		if(!array_key_exists('management_portal_access_level_id', $user) || empty($user['management_portal_access_level_id'])) {
			header('HTTP/1.0 400 Bad Request');
			die('You must specify the user\'s management portal access level');
		} else {
			$connection = DB::getConnection();
			$sql = 'SELECT id FROM management_portal_access_levels WHERE id = :id';
			$query = $connection->prepare($sql);
			$query->bindValue(':id', $user['management_portal_access_level_id']);
			if($query->execute()) {
				$type = $query->fetch(PDO::FETCH_ASSOC);
				if(!$type) {
					header('HTTP/1.0 400 Bad Request');
					die('You must specify a valid management portal access level for the user');
				}
			} else {
				header('HTTP/1.0 500 Internal Server Error');
				die('We experienced issues communicating with the database');
			}
		}
		if(!array_key_exists('authorizations', $user) || empty($user['authorizations']) || !is_array($user['authorizations'])) {
			header('HTTP/1.0 400 Bad Request');
			die('You must specify the user\'s authorizations');
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
	// trainers and admins can use this endpoint check in each method

	// only authenticated users should reach this point
	if((include_once '../lib/Database.php') === FALSE) {
		header('HTTP/1.0 500 Internal Server Error');
		die('We were unable to load some dependencies. Please ask you server administrator to investigate');
	}

	// switch on the request method
	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET':		// List/Read
			switch($_SESSION['user']['management_portal_access_level_id']) {
				case 2: // trainer intentional fallthrough same as admin
				case 3: // admin
					if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
						$connection = DB::getConnection();
						$sql = 'SELECT u.id, u.name, u.email, u.management_portal_access_level_id, mpal.name AS management_portal_access_level FROM users AS u INNER JOIN management_portal_access_levels AS mpal ON mpal.id = u.management_portal_access_level_id WHERE u.id = :id';
						$query = $connection->prepare($sql);
						$query->bindValue(':id', $_GET['id']);
						if($query->execute()) {
							if($user = $query->fetch(\PDO::FETCH_ASSOC)) {
								// join in authorizations
								$sql = 'SELECT a.id, a.equipment_type_id, e.name as equipment_type FROM authorizations AS a INNER JOIN equipment_types AS e ON e.id = a.equipment_type_id WHERE a.user_id = :id ORDER BY e.id';
								$query = $connection->prepare($sql);
								$query->bindValue(':id', $_GET['id']);
								if($query->execute()) {
									if($authorizations = $query->fetchAll(\PDO::FETCH_ASSOC)) {
										$user['authorizations'] = $authorizations;
									} else { // having no authorizations is not an error
										$user['authorizations'] = array();
									}
		
									// join in cards
									echo json_encode($user);
								} else {
									header('HTTP/1.0 500 Internal Server Error');
									//die($query->errorInfo()[2]);
									die('We experienced issues communicating with the database');
								}
							} else {
								header('HTTP/1.0 404 Not Found');
								die('We have no record of that user');
							}
						} else {
							header('HTTP/1.0 500 Internal Server Error');
							//die($query->errorInfo()[2]);
							die('We experienced issues communicating with the database');
						}
					} else { // List
						$connection = DB::getConnection();
						$sql = 'SELECT id, name, email FROM users';
						if(isset($_GET['filter']) && !empty($_GET['filter'])) {
							$sql .= ' WHERE name LIKE "%:filter%"';
						}
						if(isset($_GET['sort']) && !empty($_GET['sort'])) {
							$sort = strtolower($_GET['sort']);
							switch($sort) {
								case 'name':
									$sql .= ' ORDER BY name';
									break;
								case 'email':
									$sql .= ' ORDER BY email';
									break;
							}
						}
						$query = $connection->prepare($sql);
						if(isset($_GET['filter']) && !empty($_GET['filter'])) {
							$query->bindValue(':filter', urldecode($_GET['filter']));
						}
						if($query->execute()) {
							$users = $query->fetchAll(\PDO::FETCH_ASSOC);
							echo json_encode($users);
						} else {
							header('HTTP/1.0 500 Internal Server Error');
							//die($query->errorInfo()[2]);
							die('We experienced issues communicating with the database');
						}
					}
					break;
				default:
					header('HTTP/1.0 403 Not Authorized');
					die('You have not been granted privileges for this data.');
			}
			break;
		case 'POST':	// Update
			switch($_SESSION['user']['management_portal_access_level_id']) {
				case 2:
					if(!isset($_GET['id']) || empty($_GET['id'])) {
						header('HTTP/1.0 400 Bad Request');
						die('You must specify the user to modify via the id param');
					}

					$user = json_decode(file_get_contents('php://input'), TRUE);
					if(NULL !== $user) {
						if(!array_key_exists('authorizations', $user) || empty($user['authorizations']) || !is_array($user['authorizations'])) {
							header('HTTP/1.0 400 Bad Request');
							die('You must specify the user\'s authorizations');
						}
						$desired_authorizations = $user['authorizations'];

						// insure user exists
						$connection = DB::getConnection();
						$sql = 'SELECT * FROM users WHERE id = :id';
						$query = $connection->prepare($sql);
						$query->bindValue(':id', $_GET['id']);
						if($query->execute()) {
							if($user = $query->fetch(\PDO::FETCH_ASSOC)) {
								// update authorizations...
								// three cases: no change, granted (value is true in
								//     submission but not db), revoked (in db but not
								//     submission)
								// figure out equipment_ids with a value of true in the
								//     submission 
								$desired_authorized_equipement_type_ids = [];
								foreach($desired_authorizations as $key => $value) {
									if($value) {
										$desired_authorized_equipement_type_ids[] = $key;
									}
								}

								// figure out the authorized equipment_ids in db
								$connection = DB::getConnection();
								$sql = 'SELECT id, equipment_type_id FROM authorizations WHERE user_id = :id';
								$query = $connection->prepare($sql);
								$query->bindValue(':id', $_GET['id']);
								if($query->execute()) {
									$current_authorizations = [];
									while($authorization = $query->fetch(\PDO::FETCH_ASSOC)) {
										$current_authorizations[$authorization["id"]] = $authorization["equipment_type_id"];
									}

									// search lists to determine changes. lists should
									//     be relatively short so brute force okay 
									$authorization_granted_equipment_ids = [];
									foreach($desired_authorized_equipement_type_ids as $id) {
										if(FALSE === array_search($id, $current_authorizations)) {
											$authorization_granted_equipment_ids[] = $id;
										}
									}
									$authorizations_revoked_ids = [];
									foreach($current_authorizations as $id => $equipment_type_id) {
										if(FALSE === array_search($equipment_type_id, $desired_authorized_equipement_type_ids)) {
											$authorizations_revoked_ids[] = $id;
										}
									}

									$connection->beginTransaction();

									// Add newly granted authorizations to the db
									if(0 < count($authorization_granted_equipment_ids)) {
										$sql = 'INSERT INTO authorizations(equipment_type_id, user_id) VALUES(:equipment_type_id, :user_id)';
										$query = $connection->prepare($sql);
										foreach($authorization_granted_equipment_ids as $id) {
											$query->bindValue(':user_id', $user['id']);
											$query->bindValue(':equipment_type_id', $id);
											if(!$query->execute()) {
												$connection->rollBack();
												header('HTTP/1.0 500 Internal Server Error');
												//die($query->errorInfo()[2]);
												die('We experienced issues communicating with the database');
											}
										}
									}

									// Revoke authorizations as instructed
									if(0 < count($authorizations_revoked_ids)) {
										$sql = 'DELETE FROM authorizations WHERE id = :id';
										$query = $connection->prepare($sql);
										foreach($authorizations_revoked_ids as $id) {
											$query->bindValue(':id', $id);
											if(!$query->execute()) {
												$connection->rollBack();
												header('HTTP/1.0 500 Internal Server Error');
												//die($query->errorInfo()[2]);
												die('We experienced issues communicating with the database');
											}
										}
									}
									
									// user now in consistent state, commit and return
									$connection->commit();
									echo json_encode($user);
								} else {
									header('HTTP/1.0 500 Internal Server Error');
									//die($query->errorInfo()[2]);
									die('We experienced issues communicating with the database');
								}
							} else {
								header('HTTP/1.0 404 Not Found');
								die('We have no record of that user');
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
				case 3: // admins can do anything
					// validate that we have an oid
					if(!isset($_GET['id']) || empty($_GET['id'])) {
						header('HTTP/1.0 400 Bad Request');
						die('You must specify the user to modify via the id param');
					}

					$user = json_decode(file_get_contents('php://input'), TRUE);
					if(NULL !== $user) {
						// validate user
						validate($user);

						// okay to save to DB
						$connection = DB::getConnection();
						$sql = 'UPDATE users SET name = :name, email = :email, management_portal_access_level_id = :management_portal_access_level_id WHERE id = :id';
						$query = $connection->prepare($sql);
						$query->bindValue(':id', $_GET['id']);
						$query->bindValue(':name', $user['name']);
						$query->bindValue(':email', $user['email']);
						$query->bindValue(':management_portal_access_level_id', $user['management_portal_access_level_id']);
						$connection->beginTransaction();
						if($query->execute()) {
							// most drivers do not report the number of rows on an UPDATE
							// We'll update the value in the id field for consistency and to
							// prevent granting authorizations to the wrong user if the id in
							// the json document does not match the id passed as the get param
							$user['id'] = $_GET['id'];

							// update authorizations...
							// three cases: no change, granted (value is true in
							//     submission but not db), revoked (in db but not
							//     submission)
							// figure out equipment_ids with a value of true in the
							//     submission 
							$desired_authorized_equipement_type_ids = [];
							foreach($user['authorizations'] as $key => $value) {
								if($value) {
									$desired_authorized_equipement_type_ids[] = $key;
								}
							}

							// figure out the authorized equipment_ids in db
							$sql = 'SELECT id, equipment_type_id FROM authorizations WHERE user_id = :id';
							$query = $connection->prepare($sql);
							$query->bindValue(':id', $_GET['id']);
							if($query->execute()) {
								$current_authorizations = [];
								while($authorization = $query->fetch(\PDO::FETCH_ASSOC)) {
									$current_authorizations[$authorization["id"]] = $authorization["equipment_type_id"];
								}

								// search lists, should be relatively short to
								//     determine changes
								$authorization_granted_equipment_ids = [];
								foreach($desired_authorized_equipement_type_ids as $id) {
									if(FALSE === array_search($id, $current_authorizations)) {
										$authorization_granted_equipment_ids[] = $id;
									}
								}
								$authorizations_revoked_ids = [];
								foreach($current_authorizations as $id => $equipment_type_id) {
									if(FALSE === array_search($equipment_type_id, $desired_authorized_equipement_type_ids)) {
										$authorizations_revoked_ids[] = $id;
									}
								}

								// Add newly granted authorizations to the db
								if(0 < count($authorization_granted_equipment_ids)) {
									$sql = 'INSERT INTO authorizations(equipment_type_id, user_id) VALUES(:equipment_type_id, :user_id)';
									$query = $connection->prepare($sql);
									foreach($authorization_granted_equipment_ids as $id) {
										$query->bindValue(':user_id', $user['id']);
										$query->bindValue(':equipment_type_id', $id);
										if(!$query->execute()) {
											$connection->rollBack();
											header('HTTP/1.0 500 Internal Server Error');
											//die($query->errorInfo()[2]);
											die('We experienced issues communicating with the database');
										}
									}
								}

								// Revoke authorizations as instructed
								if(0 < count($authorizations_revoked_ids)) {
									$sql = 'DELETE FROM authorizations WHERE id = :id';
									$query = $connection->prepare($sql);
									foreach($authorizations_revoked_ids as $id) {
										$query->bindValue(':id', $id);
										if(!$query->execute()) {
											$connection->rollBack();
											header('HTTP/1.0 500 Internal Server Error');
											//die($query->errorInfo()[2]);
											die('We experienced issues communicating with the database');
										}
									}
								}
								
								// user now in consistent state, commit and return
								$connection->commit();
								echo json_encode($user);
							} else {
								$connection->rollBack();
								header('HTTP/1.0 500 Internal Server Error');
								//die($query->errorInfo()[2]);
								die('We experienced issues communicating with the database');
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
				default:
					header('HTTP/1.0 403 Not Authorized');
					die('You have not been granted privileges to modify users.');
			}
			
			break;
		case 'PUT':		// Create
			if(3 != $_SESSION['user']['management_portal_access_level_id']) {
				header('HTTP/1.0 403 Not Authorized');
				die('You have not been granted privileges to create users.');
			}
			$user = json_decode(file_get_contents('php://input'), TRUE);
			if(NULL !== $user) {
				// validate user
				validate($user);

				$connection = DB::getConnection();
				$sql = 'INSERT INTO users(name, email, management_portal_access_level_id) VALUES(:name, :email, :management_portal_access_level_id)';
				$query = $connection->prepare($sql);
				$query->bindValue(':name', $user['name']);
				$query->bindValue(':email', $user['email']);
				$query->bindValue(':management_portal_access_level_id', $user['management_portal_access_level_id']);
				$connection->beginTransaction();
				if($query->execute()) {
					// most drivers do not report the number of rows on an INSERT
					// We'll update the user with the id
					$user['id'] = $connection->lastInsertId('users_id_seq');

					// add authorizations...
					// much more straightforward than update as the user is new
					// therefore all authorizations are new
					$desired_authorized_equipement_type_ids = [];
					foreach($user['authorizations'] as $key => $value) {
						if($value) {
							$desired_authorized_equipement_type_ids[] = $key;
						}
					}

					// Add newly granted authorizations to the db
					if(0 < count($desired_authorized_equipement_type_ids)) {
						$sql = 'INSERT INTO authorizations(equipment_type_id, user_id) VALUES(:equipment_type_id, :user_id)';
						$query = $connection->prepare($sql);
						foreach($desired_authorized_equipement_type_ids as $id) {
							$query->bindValue(':user_id', $user['id']);
							$query->bindValue(':equipment_type_id', $id);
							if(!$query->execute()) {
								$connection->rollBack();
								header('HTTP/1.0 500 Internal Server Error');
								//die($query->errorInfo()[2]);
								die('We experienced issues communicating with the database');
							}
						}
					}

					// user now in consistent state, commit and return
					$connection->commit();
					echo json_encode($user);
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
		case 'DELETE':	// Delete
			// intentional fall through, deletion not allowed
		default:
			header('HTTP/1.0 405 Method Not Allowed');
			die('We were unable to understand your request.');
	}
	
?>