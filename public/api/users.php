<?php

require '../../src/autoload.php';

use Portalbox\Config;
use Portalbox\ResponseHandler;
use Portalbox\Session;

use Portalbox\Entity\Permission;

use Portalbox\Model\UserModel;

use Portalbox\Query\UserQuery;

use Portalbox\Transform\UserTransformer;


// switch on the request method
switch($_SERVER['REQUEST_METHOD']) {
	case 'GET':		// List/Read
		if(isset($_GET['id']) && !empty($_GET['id'])) {	// Read
			// check authorization
			Session::require_authorization(Permission::READ_USER);

			try {
				$model = new UserModel(Config::config());
				$user = $model->read($_GET['id']);
				if($user) {
					$transformer = new UserTransformer();
					ResponseHandler::render($user, $transformer);
				} else {
					http_response_code(404);
					die('We have no record of that user');
				}
			} catch(Exception $e) {
				http_response_code(500);
				die('We experienced issues communicating with the database');
			}
		} else { // List
			// check authorization
			Session::require_authorization(Permission::LIST_USERS);

			try {
				$model = new UserModel(Config::config());
				$query = new UserQuery();
				if(isset($_GET['email']) && !empty($_GET['email'])) {
					$query->set_email($_GET['email']);
				}

				$users = $model->search($query);
				$transformer = new UserTransformer();
				ResponseHandler::render($users, $transformer);
			} catch(Exception $e) {
				http_response_code(500);
				die('We experienced issues communicating with the database');
			}
		}
		break;
	case 'POST':	// Update
		// require_authorization('trainer');
		// $access_level = get_user_authorization_level();

		// // If exectution has reached here, the user is authorized so reasonable
		// // to do common input validation

		// // validate that we have an oid
		// if(!isset($_GET['id']) || empty($_GET['id'])) {
		// 	http_response_code(400);
		// 	die('You must specify the user to modify via the id param');
		// }

		// // validate that we have input
		// $user = json_decode(file_get_contents('php://input'), TRUE);
		// if(NULL === $user) {
		// 	http_response_code(400);
		// 	die("We could not decode your data. JSON error: " . json_last_error_msg());
		// }

		// // different access levels can do different things
		// // WARNING HARD CODED VALUE
		// if(2 < $access_level) {	// admin === 3
		// 	// validate user
		// 	validate($user);

		// 	// okay to save to DB
		// 	$connection = DB::getConnection();
		// 	$sql = 'UPDATE users SET name = :name, email = :email, comment = :comment, is_active = :is_active, management_portal_access_level_id = :management_portal_access_level_id WHERE id = :id';
		// 	$query = $connection->prepare($sql);
		// 	$query->bindValue(':id', $_GET['id'], PDO::PARAM_INT);
		// 	$query->bindValue(':name', $user['name']);
		// 	if(array_key_exists('comment', $user) && !empty($user['comment'])) { // an optional, nullable field
		// 		$query->bindValue(':comment', htmlentities($user['comment']));
		// 	} else {
		// 		$query->bindValue(':comment', NULL, PDO::PARAM_NULL);
		// 	}
		// 	$query->bindValue(':email', $user['email']);
		// 	$query->bindValue(':is_active', $user['is_active'], PDO::PARAM_BOOL);
		// 	$query->bindValue(':management_portal_access_level_id', $user['management_portal_access_level_id'], PDO::PARAM_INT);
		// 	$connection->beginTransaction();
		// 	if($query->execute()) {
		// 		// most drivers do not report the number of rows on an UPDATE
		// 		// We'll update the value in the id field for consistency and to
		// 		// prevent granting authorizations to the wrong user if the id in
		// 		// the json document does not match the id passed as the get param
		// 		$user['id'] = $_GET['id'];

		// 		// update authorizations...
		// 		// three cases: no change, granted (value is true in
		// 		//     submission but not db), revoked (in db but not
		// 		//     submission)
		// 		// figure out equipment_ids with a value of true in the
		// 		//     submission 
		// 		$desired_authorized_equipement_type_ids = [];
		// 		if(array_key_exists('authorizations', $user)) {
		// 			foreach($user['authorizations'] as $key => $value) {
		// 				if($value) {
		// 					$desired_authorized_equipement_type_ids[] = $key;
		// 				}
		// 			}
		// 		}

		// 		// figure out the authorized equipment_ids in db
		// 		$sql = 'SELECT id, equipment_type_id FROM authorizations WHERE user_id = :id';
		// 		$query = $connection->prepare($sql);
		// 		$query->bindValue(':id', $_GET['id']);
		// 		if($query->execute()) {
		// 			$current_authorizations = [];
		// 			while($authorization = $query->fetch(\PDO::FETCH_ASSOC)) {
		// 				$current_authorizations[$authorization["id"]] = $authorization["equipment_type_id"];
		// 			}

		// 			// search lists to determine changes
		// 			// should be relatively short so O(n^2) is okay
		// 			$authorization_granted_equipment_ids = [];
		// 			foreach($desired_authorized_equipement_type_ids as $id) {
		// 				if(FALSE === array_search($id, $current_authorizations)) {
		// 					$authorization_granted_equipment_ids[] = $id;
		// 				}
		// 			}
		// 			$authorizations_revoked_ids = [];
		// 			foreach($current_authorizations as $id => $equipment_type_id) {
		// 				if(FALSE === array_search($equipment_type_id, $desired_authorized_equipement_type_ids)) {
		// 					$authorizations_revoked_ids[] = $id;
		// 				}
		// 			}

		// 			// Add newly granted authorizations to the db
		// 			if(0 < count($authorization_granted_equipment_ids)) {
		// 				$sql = 'INSERT INTO authorizations(equipment_type_id, user_id) VALUES(:equipment_type_id, :user_id)';
		// 				$query = $connection->prepare($sql);
		// 				foreach($authorization_granted_equipment_ids as $id) {
		// 					$query->bindValue(':user_id', $user['id']);
		// 					$query->bindValue(':equipment_type_id', $id);
		// 					if(!$query->execute()) {
		// 						$connection->rollBack();
		// 						http_response_code(500);
		// 						//die($query->errorInfo()[2]);
		// 						die('We experienced issues communicating with the database');
		// 					}
		// 				}
		// 			}

		// 			// Revoke authorizations as instructed
		// 			if(0 < count($authorizations_revoked_ids)) {
		// 				$sql = 'DELETE FROM authorizations WHERE id = :id';
		// 				$query = $connection->prepare($sql);
		// 				foreach($authorizations_revoked_ids as $id) {
		// 					$query->bindValue(':id', $id);
		// 					if(!$query->execute()) {
		// 						$connection->rollBack();
		// 						http_response_code(500);
		// 						//die($query->errorInfo()[2]);
		// 						die('We experienced issues communicating with the database');
		// 					}
		// 				}
		// 			}
					
		// 			// user now in consistent state, commit and return
		// 			$connection->commit();

		// 			// Join in the user's authorizations... 
		// 			$sql = 'SELECT a.id, a.equipment_type_id, e.name as equipment_type FROM authorizations AS a INNER JOIN equipment_types AS e ON e.id = a.equipment_type_id WHERE a.user_id = :id ORDER BY e.id';
		// 			$query = $connection->prepare($sql);
		// 			$query->bindValue(':id', $_GET['id']);
		// 			if($query->execute()) {
		// 				if($authorizations = $query->fetchAll(\PDO::FETCH_ASSOC)) {
		// 					$user['authorizations'] = $authorizations;
		// 				} else { // having no authorizations is not an error
		// 					$user['authorizations'] = array();
		// 				}
		// 			} // failure here should not be reported as a failure

		// 			// Join in the user's cards
		// 			$sql = 'SELECT card_id FROM users_x_cards WHERE user_id = :id';
		// 			$query = $connection->prepare($sql);
		// 			$query->bindValue(':id', $_GET['id']);
		// 			if($query->execute()) {
		// 				if($cards = $query->fetchAll(\PDO::FETCH_ASSOC)) {
		// 					$user['cards'] = $cards;
		// 				} else { // having no cards is not an error
		// 					$user['cards'] = array();
		// 				}
		// 			} // failure here should not be reported as a failure

		// 			render_json($user);
		// 		} else {
		// 			$connection->rollBack();
		// 			http_response_code(500);
		// 			//die($query->errorInfo()[2]);
		// 			die('We experienced issues communicating with the database');
		// 		}
		// 	} else {
		// 		$connection->rollBack();
		// 		http_response_code(500);
		// 		//die($query->errorInfo()[2]);
		// 		die('We experienced issues communicating with the database');
		// 	}
		// } else { // not admin but at least trainer :. trainer
		// 	if(!array_key_exists('authorizations', $user) || !is_array($user['authorizations'])) {
		// 		http_response_code(400);
		// 		die('You must specify the user\'s authorizations as an array');
		// 	}

		// 	// figure out equipment_ids with a value of true in the
		// 	//     submission
		// 	$desired_authorized_equipement_type_ids = [];
		// 	foreach($user['authorizations'] as $key => $value) {
		// 		if($value) {
		// 			error_log("Trainer requests user have authorization: " . $key);
		// 			$desired_authorized_equipement_type_ids[] = $key;
		// 		}
		// 	}

		// 	// insure user exists
		// 	$connection = DB::getConnection();
		// 	$sql = 'SELECT * FROM users WHERE id = :id';
		// 	$query = $connection->prepare($sql);
		// 	$query->bindValue(':id', $_GET['id']);
		// 	if($query->execute()) {
		// 		if($user = $query->fetch(\PDO::FETCH_ASSOC)) {
		// 			// update authorizations...
		// 			// three cases: no change, granted (value is true in
		// 			//     submission but not db), revoked (in db but not
		// 			//     submission)

		// 			// figure out the authorized equipment_ids in db
		// 			$connection = DB::getConnection();
		// 			$sql = 'SELECT id, equipment_type_id FROM authorizations WHERE user_id = :id';
		// 			$query = $connection->prepare($sql);
		// 			$query->bindValue(':id', $_GET['id']);
		// 			if($query->execute()) {
		// 				$current_authorizations = [];
		// 				while($authorization = $query->fetch(\PDO::FETCH_ASSOC)) {
		// 					$current_authorizations[$authorization["id"]] = $authorization["equipment_type_id"];
		// 				}

		// 				// search lists to determine changes
		// 				// should be relatively short so O(n^2) is okay
		// 				$authorization_granted_equipment_ids = [];
		// 				foreach($desired_authorized_equipement_type_ids as $id) {
		// 					if(FALSE === array_search($id, $current_authorizations)) {
		// 						$authorization_granted_equipment_ids[] = $id;
		// 					}
		// 				}
		// 				$authorizations_revoked_ids = [];
		// 				foreach($current_authorizations as $id => $equipment_type_id) {
		// 					if(FALSE === array_search($equipment_type_id, $desired_authorized_equipement_type_ids)) {
		// 						$authorizations_revoked_ids[] = $id;
		// 					}
		// 				}

		// 				$connection->beginTransaction();

		// 				// Add newly granted authorizations to the db
		// 				if(0 < count($authorization_granted_equipment_ids)) {
		// 					$sql = 'INSERT INTO authorizations(equipment_type_id, user_id) VALUES(:equipment_type_id, :user_id)';
		// 					$query = $connection->prepare($sql);
		// 					foreach($authorization_granted_equipment_ids as $id) {
		// 						$query->bindValue(':user_id', $user['id']);
		// 						$query->bindValue(':equipment_type_id', $id);
		// 						if(!$query->execute()) {
		// 							$connection->rollBack();
		// 							http_response_code(500);
		// 							//die($query->errorInfo()[2]);
		// 							die('We experienced issues communicating with the database');
		// 						}
		// 					}
		// 				}

		// 				// Revoke authorizations as instructed
		// 				if(0 < count($authorizations_revoked_ids)) {
		// 					$sql = 'DELETE FROM authorizations WHERE id = :id';
		// 					$query = $connection->prepare($sql);
		// 					foreach($authorizations_revoked_ids as $id) {
		// 						$query->bindValue(':id', $id);
		// 						if(!$query->execute()) {
		// 							$connection->rollBack();
		// 							http_response_code(500);
		// 							//die($query->errorInfo()[2]);
		// 							die('We experienced issues communicating with the database');
		// 						}
		// 					}
		// 				}

		// 				// user now in consistent state, commit and return
		// 				$connection->commit();

		// 				// Should return the user's resultant authorizations... 
		// 				$sql = 'SELECT a.id, a.equipment_type_id, e.name as equipment_type FROM authorizations AS a INNER JOIN equipment_types AS e ON e.id = a.equipment_type_id WHERE a.user_id = :id ORDER BY e.id';
		// 				$query = $connection->prepare($sql);
		// 				$query->bindValue(':id', $_GET['id']);
		// 				if($query->execute()) {
		// 					if($authorizations = $query->fetchAll(\PDO::FETCH_ASSOC)) {
		// 						$user['authorizations'] = $authorizations;
		// 					} else { // having no authorizations is not an error
		// 						$user['authorizations'] = array();
		// 					}
		// 				} // failure here should not be reported as a failure

		// 				// Join in the user's cards
		// 				$sql = 'SELECT card_id FROM users_x_cards WHERE user_id = :id';
		// 				$query = $connection->prepare($sql);
		// 				$query->bindValue(':id', $_GET['id']);
		// 				if($query->execute()) {
		// 					if($cards = $query->fetchAll(\PDO::FETCH_ASSOC)) {
		// 						$user['cards'] = $cards;
		// 					} else { // having no cards is not an error
		// 						$user['cards'] = array();
		// 					}
		// 				} // failure here should not be reported as a failure

		// 				render_json($user);
		// 			} else {
		// 				http_response_code(500);
		// 				//die($query->errorInfo()[2]);
		// 				die('We experienced issues communicating with the database');
		// 			}
		// 		} else {
		// 			http_response_code(404);
		// 			die('We have no record of that user');
		// 		}
		// 	} else {
		// 		http_response_code(500);
		// 		//die($query->errorInfo()[2]);
		// 		die('We experienced issues communicating with the database');
		// 	}
		// }
		break;
	case 'PUT':		// Create
		// require_authorization('admin');
		// $user = json_decode(file_get_contents('php://input'), TRUE);
		// if(NULL !== $user) {
		// 	// validate user
		// 	validate($user);

		// 	$connection = DB::getConnection();

		// 	// insure user does NOT exist
		// 	$connection = DB::getConnection();
		// 	$sql = 'SELECT * FROM users WHERE email = :email';
		// 	$query = $connection->prepare($sql);
		// 	$query->bindValue(':email', $user['email']);
		// 	if($query->execute()) {
		// 		if(0 < $query->rowCount()) {
		// 			http_response_code(403);
		// 			//die($query->errorInfo()[2]);
		// 			die('A user with the provided email address can not be added to the database');
		// 		}
		// 	} else {
		// 		http_response_code(500);
		// 		//die($query->errorInfo()[2]);
		// 		die('We experienced issues communicating with the database');
		// 	}

		// 	// Should be safe to add user... (DB CONstraint will prevent race condition)
		// 	$sql = 'INSERT INTO users(name, email, comment, is_active, management_portal_access_level_id) VALUES(:name, :email, :comment, :is_active, :management_portal_access_level_id)';
		// 	$query = $connection->prepare($sql);
		// 	$query->bindValue(':name', $user['name']);
		// 	$query->bindValue(':email', $user['email']);
		// 	if(array_key_exists('comment', $user) && !empty($user['comment'])) { // an optional, nullable field
		// 		$query->bindValue(':comment', htmlentities($user['comment']));
		// 	} else {
		// 		$query->bindValue(':comment', NULL, PDO::PARAM_NULL);
		// 	}
		// 	$query->bindValue(':is_active', $user['is_active'], PDO::PARAM_BOOL);
		// 	$query->bindValue(':management_portal_access_level_id', $user['management_portal_access_level_id'], PDO::PARAM_INT);
		// 	$connection->beginTransaction();
		// 	if($query->execute()) {
		// 		// most drivers do not report the number of rows on an INSERT
		// 		// We'll update the user with the id
		// 		$user['id'] = $connection->lastInsertId('users_id_seq');

		// 		// add authorizations...
		// 		// much more straightforward than update as the user is new
		// 		// therefore all authorizations are new
		// 		$desired_authorized_equipement_type_ids = [];
		// 		if(array_key_exists('authorizations', $user)) {
		// 			foreach($user['authorizations'] as $key => $value) {
		// 				if($value) {
		// 					$desired_authorized_equipement_type_ids[] = $key;
		// 				}
		// 			}
		// 		}

		// 		// Add newly granted authorizations to the db
		// 		if(0 < count($desired_authorized_equipement_type_ids)) {
		// 			$sql = 'INSERT INTO authorizations(equipment_type_id, user_id) VALUES(:equipment_type_id, :user_id)';
		// 			$query = $connection->prepare($sql);
		// 			foreach($desired_authorized_equipement_type_ids as $id) {
		// 				$query->bindValue(':user_id', $user['id']);
		// 				$query->bindValue(':equipment_type_id', $id);
		// 				if(!$query->execute()) {
		// 					$connection->rollBack();
		// 					http_response_code(500);
		// 					//die($query->errorInfo()[2]);
		// 					die('We experienced issues communicating with the database');
		// 				}
		// 			}
		// 		}

		// 		// User does not have any cards
		// 		$user['cards'] = array();

		// 		// user now in consistent state, commit and return
		// 		$connection->commit();
		// 		render_json($user);
		// 	} else {
		// 		$connection->rollBack();
		// 		http_response_code(500);
		// 		//die($query->errorInfo()[2]);
		// 		die('We experienced issues communicating with the database');
		// 	}
		// } else {
		// 	http_response_code(400);
		// 	die("We could not decode your data. JSON error: " . json_last_error_msg());
		// }
		break;
	case 'DELETE':	// Delete
		// intentional fall through, deletion not allowed
	default:
		http_response_code(405);
		die('We were unable to understand your request.');
}
