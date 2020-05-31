<?php
	/**
	 * validate - check that the parameter is an associative array with non-
	 * empty values for the 'name' and 'email' keys. If all is well returns
	 * but if a check fails; the proper HTTP response is emitted and execution
	 * is halted.
	 */
	function validate(&$user) {
		// do not warn if include fails
		@include_once('../lib/extensions/ext_validate_email.php');
		if(!is_array($user)) {
			http_response_code(500);
			die('We seem to have encountered an unexpected difficulty. Please ask your server administrator to investigate');
		}
		if(!array_key_exists('name', $user) || empty($user['name'])) {
			http_response_code(400);
			die('You must specify the user\'s name');
		}
		if(!array_key_exists('email', $user) || empty($user['email'])) {
			http_response_code(400);
			die('You must specify the user\'s email');
		} else {
			if(function_exists('ext_validate_email')) {
				$email = ext_validate_email($user['email']);
				if(FALSE === $email) {
					http_response_code(400);
					die('You must specify a valid email for the user');
				} else {
					// "return" possibly modified value through pass by reference mechanism
					$user['email'] = $email;
				}
			}
		}
		if(!array_key_exists('management_portal_access_level_id', $user) || empty($user['management_portal_access_level_id'])) {
			http_response_code(400);
			die('You must specify the user\'s management portal access level');
		} else {
			$connection = DB::getConnection();
			$sql = 'SELECT id FROM management_portal_access_levels WHERE id = :id';
			$query = $connection->prepare($sql);
			$query->bindValue(':id', $user['management_portal_access_level_id']);
			if($query->execute()) {
				$type = $query->fetch(PDO::FETCH_ASSOC);
				if(!$type) {
					http_response_code(400);
					die('You must specify a valid management portal access level for the user');
				}
			} else {
				http_response_code(500);
				die('We experienced issues communicating with the database');
			}
		}
		if(!array_key_exists('is_active', $user) || !isset($user['is_active'])) {
			http_response_code(400);
			die('You must specify whether the user is active');
		}
		if(array_key_exists('authorizations', $user) && !is_array($user['authorizations'])) {
			http_response_code(400);
			die('You must specify the user\'s authorizations as an array');
		}
	}

	// check authentication
	// trainers and admins can use this endpoint, we'll have to check authorization in each method
	if((include_once '../lib/Security.php') === FALSE) {
		http_response_code(500);
		die('We were unable to load some dependencies. Please ask your server administrator to investigate');
	}
	require_authentication();

	// only authenticated users should reach this point
	if((include_once '../lib/Database.php') === FALSE) {
		http_response_code(500);
		die('We were unable to load some dependencies. Please ask your server administrator to investigate');
	}

	if((include_once '../lib/EncodeOutput.php') === FALSE) {
		http_response_code(500);
		die('We were unable to load some dependencies. Please ask your server administrator to investigate');
	}

	// switch on the request method
	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET':		// List/Read
			if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
				if($_GET['id'] != SecurityContext::getContext()->authorized_user_id) {	// allow users to view own profile
					require_authorization('trainer');
				} 
				$connection = DB::getConnection();
				$sql = 'SELECT u.id, u.name, u.email, u.comment, u.is_active, u.management_portal_access_level_id, mpal.name AS management_portal_access_level FROM users AS u INNER JOIN management_portal_access_levels AS mpal ON mpal.id = u.management_portal_access_level_id WHERE u.id = :id';
				$query = $connection->prepare($sql);
				$query->bindValue(':id', $_GET['id']);
				if($query->execute()) {
					if($user = $query->fetch(\PDO::FETCH_ASSOC)) {
						// recast is_active as a boolean
						if($user['is_active']) {
							$user['is_active'] = true;
						} else {
							$user['is_active'] = false;
						}

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
							$sql = 'SELECT card_id FROM users_x_cards WHERE user_id = :id';
							$query = $connection->prepare($sql);
							$query->bindValue(':id', $_GET['id']);
							if($query->execute()) {
								if($cards = $query->fetchAll(\PDO::FETCH_ASSOC)) {
									$user['cards'] = $cards;
								} else { // having no cards is not an error
									$user['cards'] = array();
								}

								render_json($user);
							} else {
								http_response_code(500);
								//die($query->errorInfo()[2]);
								die('We experienced issues communicating with the database');
							}
						} else {
							http_response_code(500);
							//die($query->errorInfo()[2]);
							die('We experienced issues communicating with the database');
						}
					} else {
						http_response_code(404);
						die('We have no record of that user');
					}
				} else {
					http_response_code(500);
					//die($query->errorInfo()[2]);
					die('We experienced issues communicating with the database');
				}
			} else { // List
				require_authorization('trainer');

				// purposefully omitting comment from listing, though one can search on it
				$connection = DB::getConnection();
				$sql = 'SELECT u.id, u.name, u.email, u.is_active, mpal.name AS management_portal_access_level FROM users AS u INNER JOIN management_portal_access_levels AS mpal ON mpal.id = u.management_portal_access_level_id';

				// prepare filtering
				$where_clause_fragments = array();
				if(isset($_GET['name']) && !empty($_GET['name'])) {
					$where_clause_fragments[] = 'u.name LIKE :name';
				}
				if(isset($_GET['comment']) && !empty($_GET['comment'])) {
					$where_clause_fragments[] = 'u.comment LIKE :comment';
				}
				if(isset($_GET['include_inactive']) && !empty($_GET['include_inactive'])) {
					// do nothing i.e. do not filter for in service only
				} else {
					$where_clause_fragments[] = 'u.is_active = 1';
				}
				if(0 < count($where_clause_fragments)) {
					$sql .= ' WHERE ';
					$sql .= join(' AND ', $where_clause_fragments);
				}

				// prepare sorting
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

				// execute query
				$query = $connection->prepare($sql);
				if(isset($_GET['name']) && !empty($_GET['name'])) {
					$query->bindValue(':name', '%' . urldecode($_GET['name']) . '%');
				}
				if(isset($_GET['comment']) && !empty($_GET['comment'])) {
					$query->bindValue(':comment', '%' . urldecode($_GET['comment']) . '%');
				}
				if($query->execute()) {
					$users = $query->fetchAll(\PDO::FETCH_ASSOC);
					// recast is_active as a boolean
					foreach($users as &$u) {
						if($u['is_active']) {
							$u['is_active'] = true;
						} else {
							$u['is_active'] = false;
						}
					}
					unset($u);
					render_json($users);
				} else {
					http_response_code(500);
					//die($query->errorInfo()[2]);
					die('We experienced issues communicating with the database');
				}
			}	
			break;
		case 'POST':	// Update
			require_authorization('trainer');
			$access_level = get_user_authorization_level();

			// If exectution has reached here, the user is authorized so reasonable
			// to do common input validation

			// validate that we have an oid
			if(!isset($_GET['id']) || empty($_GET['id'])) {
				http_response_code(400);
				die('You must specify the user to modify via the id param');
			}

			// validate that we have input
			$user = json_decode(file_get_contents('php://input'), TRUE);
			if(NULL === $user) {
				http_response_code(400);
				die("We could not decode your data. JSON error: " . json_last_error_msg());
			}

			// different access levels can do different things
			// WARNING HARD CODED VALUE
			if(2 < $access_level) {	// admin === 3
				// validate user
				validate($user);

				// okay to save to DB
				$connection = DB::getConnection();
				$sql = 'UPDATE users SET name = :name, email = :email, comment = :comment, is_active = :is_active, management_portal_access_level_id = :management_portal_access_level_id WHERE id = :id';
				$query = $connection->prepare($sql);
				$query->bindValue(':id', $_GET['id'], PDO::PARAM_INT);
				$query->bindValue(':name', $user['name']);
				if(array_key_exists('comment', $user) && !empty($user['comment'])) { // an optional, nullable field
					$query->bindValue(':comment', htmlentities($user['comment']));
				} else {
					$query->bindValue(':comment', NULL, PDO::PARAM_NULL);
				}
				$query->bindValue(':email', $user['email']);
				$query->bindValue(':is_active', $user['is_active'], PDO::PARAM_BOOL);
				$query->bindValue(':management_portal_access_level_id', $user['management_portal_access_level_id'], PDO::PARAM_INT);
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
					if(array_key_exists('authorizations', $user)) {
						foreach($user['authorizations'] as $key => $value) {
							if($value) {
								$desired_authorized_equipement_type_ids[] = $key;
							}
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

						// search lists to determine changes
						// should be relatively short so O(n^2) is okay
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
									http_response_code(500);
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
									http_response_code(500);
									//die($query->errorInfo()[2]);
									die('We experienced issues communicating with the database');
								}
							}
						}
						
						// user now in consistent state, commit and return
						$connection->commit();

						// Join in the user's authorizations... 
						$sql = 'SELECT a.id, a.equipment_type_id, e.name as equipment_type FROM authorizations AS a INNER JOIN equipment_types AS e ON e.id = a.equipment_type_id WHERE a.user_id = :id ORDER BY e.id';
						$query = $connection->prepare($sql);
						$query->bindValue(':id', $_GET['id']);
						if($query->execute()) {
							if($authorizations = $query->fetchAll(\PDO::FETCH_ASSOC)) {
								$user['authorizations'] = $authorizations;
							} else { // having no authorizations is not an error
								$user['authorizations'] = array();
							}
						} // failure here should not be reported as a failure

						// Join in the user's cards
						$sql = 'SELECT card_id FROM users_x_cards WHERE user_id = :id';
						$query = $connection->prepare($sql);
						$query->bindValue(':id', $_GET['id']);
						if($query->execute()) {
							if($cards = $query->fetchAll(\PDO::FETCH_ASSOC)) {
								$user['cards'] = $cards;
							} else { // having no cards is not an error
								$user['cards'] = array();
							}
						} // failure here should not be reported as a failure

						render_json($user);
					} else {
						$connection->rollBack();
						http_response_code(500);
						//die($query->errorInfo()[2]);
						die('We experienced issues communicating with the database');
					}
				} else {
					$connection->rollBack();
					http_response_code(500);
					//die($query->errorInfo()[2]);
					die('We experienced issues communicating with the database');
				}
			} else { // not admin but at least trainer :. trainer
				if(!array_key_exists('authorizations', $user) || !is_array($user['authorizations'])) {
					http_response_code(400);
					die('You must specify the user\'s authorizations as an array');
				}

				// figure out equipment_ids with a value of true in the
				//     submission
				$desired_authorized_equipement_type_ids = [];
				foreach($user['authorizations'] as $key => $value) {
					if($value) {
						error_log("Trainer requests user have authorization: " . $key);
						$desired_authorized_equipement_type_ids[] = $key;
					}
				}

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

							// search lists to determine changes
							// should be relatively short so O(n^2) is okay
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
										http_response_code(500);
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
										http_response_code(500);
										//die($query->errorInfo()[2]);
										die('We experienced issues communicating with the database');
									}
								}
							}

							// user now in consistent state, commit and return
							$connection->commit();

							// Should return the user's resultant authorizations... 
							$sql = 'SELECT a.id, a.equipment_type_id, e.name as equipment_type FROM authorizations AS a INNER JOIN equipment_types AS e ON e.id = a.equipment_type_id WHERE a.user_id = :id ORDER BY e.id';
							$query = $connection->prepare($sql);
							$query->bindValue(':id', $_GET['id']);
							if($query->execute()) {
								if($authorizations = $query->fetchAll(\PDO::FETCH_ASSOC)) {
									$user['authorizations'] = $authorizations;
								} else { // having no authorizations is not an error
									$user['authorizations'] = array();
								}
							} // failure here should not be reported as a failure

							// Join in the user's cards
							$sql = 'SELECT card_id FROM users_x_cards WHERE user_id = :id';
							$query = $connection->prepare($sql);
							$query->bindValue(':id', $_GET['id']);
							if($query->execute()) {
								if($cards = $query->fetchAll(\PDO::FETCH_ASSOC)) {
									$user['cards'] = $cards;
								} else { // having no cards is not an error
									$user['cards'] = array();
								}
							} // failure here should not be reported as a failure

							render_json($user);
						} else {
							http_response_code(500);
							//die($query->errorInfo()[2]);
							die('We experienced issues communicating with the database');
						}
					} else {
						http_response_code(404);
						die('We have no record of that user');
					}
				} else {
					http_response_code(500);
					//die($query->errorInfo()[2]);
					die('We experienced issues communicating with the database');
				}
			}
			break;
		case 'PUT':		// Create
			require_authorization('admin');
			$user = json_decode(file_get_contents('php://input'), TRUE);
			if(NULL !== $user) {
				// validate user
				validate($user);

				$connection = DB::getConnection();

				// insure user does NOT exist
				$connection = DB::getConnection();
				$sql = 'SELECT * FROM users WHERE email = :email';
				$query = $connection->prepare($sql);
				$query->bindValue(':email', $user['email']);
				if($query->execute()) {
					if(0 < $query->rowCount()) {
						http_response_code(403);
						//die($query->errorInfo()[2]);
						die('A user with the provided email address can not be added to the database');
					}
				} else {
					http_response_code(500);
					//die($query->errorInfo()[2]);
					die('We experienced issues communicating with the database');
				}

				// Should be safe to add user... (DB CONstraint will prevent race condition)
				$sql = 'INSERT INTO users(name, email, comment, is_active, management_portal_access_level_id) VALUES(:name, :email, :comment, :is_active, :management_portal_access_level_id)';
				$query = $connection->prepare($sql);
				$query->bindValue(':name', $user['name']);
				$query->bindValue(':email', $user['email']);
				if(array_key_exists('comment', $user) && !empty($user['comment'])) { // an optional, nullable field
					$query->bindValue(':comment', htmlentities($user['comment']));
				} else {
					$query->bindValue(':comment', NULL, PDO::PARAM_NULL);
				}
				$query->bindValue(':is_active', $user['is_active'], PDO::PARAM_BOOL);
				$query->bindValue(':management_portal_access_level_id', $user['management_portal_access_level_id'], PDO::PARAM_INT);
				$connection->beginTransaction();
				if($query->execute()) {
					// most drivers do not report the number of rows on an INSERT
					// We'll update the user with the id
					$user['id'] = $connection->lastInsertId('users_id_seq');

					// add authorizations...
					// much more straightforward than update as the user is new
					// therefore all authorizations are new
					$desired_authorized_equipement_type_ids = [];
					if(array_key_exists('authorizations', $user)) {
						foreach($user['authorizations'] as $key => $value) {
							if($value) {
								$desired_authorized_equipement_type_ids[] = $key;
							}
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
								http_response_code(500);
								//die($query->errorInfo()[2]);
								die('We experienced issues communicating with the database');
							}
						}
					}

					// User does not have any cards
					$user['cards'] = array();

					// user now in consistent state, commit and return
					$connection->commit();
					render_json($user);
				} else {
					$connection->rollBack();
					http_response_code(500);
					//die($query->errorInfo()[2]);
					die('We experienced issues communicating with the database');
				}
			} else {
				http_response_code(400);
				die("We could not decode your data. JSON error: " . json_last_error_msg());
			}
			break;
		case 'DELETE':	// Delete
			// intentional fall through, deletion not allowed
		default:
			http_response_code(405);
			die('We were unable to understand your request.');
	}
?>