<?php

require '../../src/autoload.php';

use PDO;
use Portalbox\Config;
use Portalbox\ResponseHandler;
use Portalbox\Session;

use Portalbox\Entity\Permission;

use Portalbox\Model\EquipmentModel;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Model\LocationModel;
use Portalbox\Exception\DatabaseException;
use Portalbox\Query\EquipmentQuery;

use Portalbox\Transform\EquipmentTransformer;
use Portalbox\Model\CardModel;

use Portalbox\Query\CardQuery;

use Portalbox\Transform\CardTransformer;






// switch on the request method
switch($_SERVER['REQUEST_METHOD']) {
	case 'GET':	
		Session::require_authorization(Permission::READ_EQUIPMENT);
		
		$path = realpath(__DIR__ . DIRECTORY_SEPARATOR .'../../config/config.ini');
		$settings = parse_ini_file($path, TRUE);
		$dsn = 'host=' . $settings['database']['host'] . ';dbname=' . $settings['database']['database'];
		$connection = new PDO($settings['database']['driver'] . ':' . $dsn, $settings['database']['username'], $settings['database']['password']);
		
		if($_GET['mode'] == "get_card_details"){
			if((isset($_GET['card_id']) && !empty($_GET['card_id'])) && (isset($_GET['equipment_id']) && !empty($_GET['equipment_id']))) {
				//Get user details 
				$card_id = $_GET['card_id'];
				$e_id = $_GET['equipment_id'];


				//Is the user authorized?
				$sql = 'SELECT count(u.id) FROM users_x_cards AS u INNER JOIN authorizations AS a ON a.user_id= u.user_id WHERE u.card_id = :card_id AND a.equipment_type_id = :e_id';
				$query = $connection->prepare($sql);
				$query->bindValue(':card_id', $card_id);	//BIGINT
				$query->bindValue(':e_id', $e_id);	//BIGINT
				
				if($query->execute()) {
					$user_auth = $query->fetch(PDO::FETCH_NUM)[0];
				} else {
					echo "Database Exception: " . $query->errorInfo()[2];
					throw new DatabaseException($query->errorInfo()[2]);
				}


				//What is the user's balance 
				$sql = 'SELECT get_user_balance_for_card(:card_id)';
				$query = $connection->prepare($sql);
				$query->bindValue(':card_id', $card_id);	//BIGINT
				if($query->execute()) {
					$user_balance = $query->fetch(PDO::FETCH_NUM)[0];
				} else {
					echo "Database Exception: " . $query->errorInfo()[2];
					throw new DatabaseException($query->errorInfo()[2]);
				}

				//Is the user active?
				$sql = 'SELECT u.is_active FROM users AS u INNER JOIN users_x_cards AS uxc ON uxc.user_id = u.id WHERE uxc.card_id = :card_id';
				$query = $connection->prepare($sql);
				$query->bindValue(':card_id', $card_id);
				if($query->execute()) {
					$user_active = $query->fetch(PDO::FETCH_NUM)[0];
				} else {
					echo "Database Exception: " . $query->errorInfo()[2];
					throw new DatabaseException($query->errorInfo()[2]);
				}

				//What is the card type?
				$sql = 'SELECT type_id FROM cards WHERE id = :card_id';
				$query = $connection->prepare($sql);
				$query->bindValue(':card_id', $card_id);
				if($query->execute()) {
					$card_type = $query->fetch(PDO::FETCH_NUM)[0];
				} else {
					echo "Database Exception: " . $query->errorInfo()[2];
					throw new DatabaseException($query->errorInfo()[2]);
				}		
				
				//Whats the user's role
				$sql = 'SELECT role_id FROM users_x_cards AS c JOIN users AS u ON u.id = c.user_id WHERE c.card_id = :card_id';
				$query = $connection->prepare($sql);
				$query->bindValue(':card_id', $card_id);	//BIGINT
				if($query->execute()) {
					$user_role = $query->fetch(PDO::FETCH_NUM)[0];
				} else {
					echo "Database Exception: " . $query->errorInfo()[2];
					throw new DatabaseException($query->errorInfo()[2]);
				}	
				
				$r_array = [[
					'user_auth' => $user_auth,
					'user_balance' => $user_balance,
					'user_active' => $user_active,
					'card_type' => $card_type,
					'user_role' => $user_role
				]];
				
				try{
					ResponseHandler::render($r_array);
				}
				catch (Exception $e) {
					echo 'Caught exception: ',  $e->getMessage(), "\n";
				}
				
			}
			else{
				http_response_code(404);
				die('Needs "card_id" and "equipment_id"');
			}
		}
		elseif($_GET['mode'] == "check_reg"){
			if(isset($_GET['mac_adr']) && !empty($_GET['mac_adr'])){
				
				$mac_adr = $_GET['mac_adr'];
			
				//Check if the box is registered 
				$sql = 'SELECT count(id) FROM equipment WHERE mac_address = :mac_adr';
				$query = $connection->prepare($sql);
				$query->bindValue(':mac_adr', $mac_adr);	//BIGINT
				if($query->execute()) {
					$registered = $query->fetch(PDO::FETCH_NUM)[0];
					echo $registered;
				} else {
					echo "Database Exception: " . $query->errorInfo()[2];
					throw new DatabaseException($query->errorInfo()[2]);
				}
			}else{
				http_response_code(404);
				die('Need Mac address');
			}
		}
		elseif($_GET['mode'] == "get_profile"){
			if(isset($_GET['mac_adr']) && !empty($_GET['mac_adr'])){
				$mac_adr = $_GET['mac_adr'];
				//Check if the box is registered 
				$sql = 'SELECT e.id, e.type_id, t.name, e.location_id, l.name, e.timeout, t.allow_proxy, t.requires_training, t.charge_policy_id > 2 AS "charge_policy"
				FROM equipment AS e 
				INNER JOIN equipment_types AS t ON e.type_id = t.id 
				INNER JOIN locations AS l ON e.location_id =  l.id 
				WHERE e.mac_address = :mac_adr';
				$query = $connection->prepare($sql);
				$query->bindValue(':mac_adr', $mac_adr);
				if($query->execute()) {
					$profile = $query->fetch(PDO::FETCH_NAMED);
					ResponseHandler::render([$profile]);
				} else {
					echo "Database Exception: " . $query->errorInfo()[2];
					throw new DatabaseException($query->errorInfo()[2]);
				}
			}else{
				http_response_code(404);
				die('Need "mac_adr"');
			}
		}
		elseif($_GET['mode'] == "get_equipment_name"){
			if(isset($_GET['equipment_id']) && !empty($_GET['equipment_id'])){
				//Check if the box is registered 
				$sql = 'SELECT name FROM equipment WHERE id = :equipment_id';
				$query = $connection->prepare($sql);
				$query->bindValue(':equipment_id', $_GET['equipment_id']);
				if($query->execute()) {
					$profile = $query->fetch(PDO::FETCH_ASSOC);
					ResponseHandler::render([$profile]);
				} else {
					echo "Database Exception: " . $query->errorInfo()[2];
					throw new DatabaseException($query->errorInfo()[2]);
				}
			}else{
				http_response_code(404);
				die('Need "equipment_id"');
			}
		}
		elseif($_GET['mode'] == "get_user"){
			if(isset($_GET['card_id']) && !empty($_GET['card_id'])){
				//Check if the box is registered 
				$sql = 'SELECT u.name, u.email FROM users_x_cards AS c JOIN users AS u ON u.id = c.user_id WHERE c.card_id = :card_id';
				$query = $connection->prepare($sql);
				$query->bindValue(':card_id', $_GET['card_id']);
				if($query->execute()) {
					$profile = $query->fetch(PDO::FETCH_ASSOC);
					ResponseHandler::render([$profile]);
				} else {
					echo "Database Exception: " . $query->errorInfo()[2];
					throw new DatabaseException($query->errorInfo()[2]);
				}
			}else{
				http_response_code(404);
				die('Need "equipment_id"');
			}
		}
		elseif(!(isset($_GET['mode']) && !empty($_GET['mode']))){
			http_response_code(404);
			die('Missing "mode", options are "get_card_details", "check_reg", "get_equipment_name", and "get_profile"');	
		}
		else{
			http_response_code(404);
			die('Not a valid mode, options are "get_card_details", "check_reg", "get_equipment_name" and "get_profile"');				
		}
		break;
	case 'POST':	// Update
		Session::require_authorization(Permission::READ_EQUIPMENT);


		$path = realpath(__DIR__ . DIRECTORY_SEPARATOR .'../../config/config.ini');
		$settings = parse_ini_file($path, TRUE);
		$dsn = 'host=' . $settings['database']['host'] . ';dbname=' . $settings['database']['database'];
		$connection = new PDO($settings['database']['driver'] . ':' . $dsn, $settings['database']['username'], $settings['database']['password']);
		
		if($_GET['mode'] == "log_access_attempt"){

			if(!isset($_GET['successful'])){
				http_response_code(404);
				die('missing params needs "successful", "card_id", and "equiment_id". Failed at "successful"');	
			}
			if(!(isset($_GET['card_id']) && !empty($_GET['card_id']))){
				http_response_code(404);
				die('missing params needs "successful", "card_id", and "equiment_id". Failed at "card_id"');	
			}
			if(!(isset($_GET['equipment_id']) && !empty($_GET['equipment_id']))){
				http_response_code(404);
				die('missing params needs "successful", "card_id", and "equiment_id". Failed at "equiment_id"');	
			}
			


			$sql = "CALL log_access_attempt(:successful, :card_id, :equiment_id)";
			$query = $connection->prepare($sql);
			$query->bindValue(':successful', $_GET['successful']);
			$query->bindValue(':card_id', $_GET['card_id']);
			$query->bindValue(':equiment_id', $_GET['equipment_id']);
			if($query->execute()) {
				echo "completed succesfully";
			} else {
				echo "Database Exception: " . $query->errorInfo()[2];
				throw new DatabaseException($query->errorInfo()[2]);
			}
			
		}
		elseif($_GET['mode'] == "log_access_completion"){

			if(!(isset($_GET['card_id']) && !empty($_GET['card_id']))){
				http_response_code(404);
				die('missing params needs "card_id", and "equiment_id". Failed at "card_id"');	
			}
			if(!(isset($_GET['equipment_id']) && !empty($_GET['equipment_id']))){
				http_response_code(404);
				die('missing params needs "card_id", and "equiment_id". Failed at "equiment_id"');	
			}
			


			$sql = "CALL log_access_completion(:card_id, :equiment_id)";
			$query = $connection->prepare($sql);
			$query->bindValue(':card_id', $_GET['card_id']);
			$query->bindValue(':equiment_id', $_GET['equipment_id']);
			if($query->execute()) {
				echo "completed succesfully";
			} else {
				echo "Database Exception: " . $query->errorInfo()[2];
				throw new DatabaseException($query->errorInfo()[2]);
			}
		}
		elseif($_GET['mode'] == "log_shutdown_status"){

			if(!(isset($_GET['card_id']) && !empty($_GET['card_id']))){
				http_response_code(404);
				die('missing params needs "card_id", and "equiment_id". Failed at "card_id"');	
			}
			if(!(isset($_GET['equipment_id']) && !empty($_GET['equipment_id']))){
				http_response_code(404);
				die('missing params needs "card_id", and "equiment_id". Failed at "equiment_id"');	
			}
			


			$sql = 'INSERT INTO log(event_type_id, equipment_id, card_id) 
					(SELECT id, :equiment_id, :card_id FROM event_types 
					WHERE name = "Planned Shutdown")';
			$query = $connection->prepare($sql);
			$query->bindValue(':equiment_id', $_GET['equipment_id']);
			$query->bindValue(':card_id', $_GET['card_id']);
			
			if($query->execute()) {
				echo "completed succesfully";
			} else {
				echo "Database Exception: " . $query->errorInfo()[2];
				throw new DatabaseException($query->errorInfo()[2]);
			}
		}
		elseif($_GET['mode'] == "log_started_status"){

			if(!(isset($_GET['equipment_id']) && !empty($_GET['equipment_id']))){
				http_response_code(404);
				die('missing params needs "equiment_id". Failed at "equiment_id"');	
			}
			


			$sql = 'INSERT INTO log(event_type_id, equipment_id) 
					(SELECT id, :equiment_id FROM event_types 
					WHERE name = "Startup Complete")';
			$query = $connection->prepare($sql);
			$query->bindValue(':equiment_id', $_GET['equipment_id']);
			
			if($query->execute()) {
				echo "Completed succesfully";
			} else {
				echo "Database Exception: " . $query->errorInfo()[2];
				throw new DatabaseException($query->errorInfo()[2]);
			}
		}
		elseif($_GET['mode'] == "record_ip"){

			if(!(isset($_GET['equipment_id']) && !empty($_GET['equipment_id']))){
				http_response_code(404);
				die('missing params needs "equiment_id". Failed at "equiment_id"');	
			}
			if(!(isset($_GET['ip_address']) && !empty($_GET['ip_address']))){
				http_response_code(404);
				die('missing params needs "ip_address". Failed at "ip_address"');	
			}
			


			$sql = 'UPDATE equipment
					SET ip_address = :ip_address
					WHERE id = :equiment_id';
			$query = $connection->prepare($sql);
			$query->bindValue(':equiment_id', $_GET['equipment_id']);
			$query->bindValue(':ip_address', $_GET['ip_address']);
			
			if($query->execute()) {
				echo "Completed succesfully";
			} else {
				echo "Database Exception: " . $query->errorInfo()[2];
				throw new DatabaseException($query->errorInfo()[2]);
			}

		}
		elseif(!(isset($_GET['mode']) && !empty($_GET['mode']))){
			http_response_code(404);
			die('Missing "mode", options are "log_access_attempt", "log_access_completion", "log_shutdown_status", "log_started_status", and "record_ip"');	
		}
		else{
			http_response_code(404);
			die('Not a valid mode, options are "log_access_attempt", "log_access_completion", "log_shutdown_status", "log_started_status", and "record_ip"');				
		}
		break;
	case 'PUT':		// Create
		Session::require_authorization(Permission::READ_EQUIPMENT);


		$path = realpath(__DIR__ . DIRECTORY_SEPARATOR .'../../config/config.ini');
		$settings = parse_ini_file($path, TRUE);
		$dsn = 'host=' . $settings['database']['host'] . ';dbname=' . $settings['database']['database'];
		$connection = new PDO($settings['database']['driver'] . ':' . $dsn, $settings['database']['username'], $settings['database']['password']);
		
		if($_GET['mode'] == "register"){
			if(isset($_GET['mac_adr']) && !empty($_GET['mac_adr'])){
				$mac_adr = $_GET['mac_adr'];
				//Check if the box is registered 
				$sql = 'INSERT INTO equipment (name, type_id, mac_address, location_id) VALUES ("New Portal Box", 1, :mac_adr, 1)';
				$query = $connection->prepare($sql);
				$query->bindValue(':mac_adr', $mac_adr);
				if($query->execute()) {
					echo "Completed succesfully";
				} else {
					echo "Database Exception: " . $query->errorInfo()[2];
					throw new DatabaseException($query->errorInfo()[2]);
				}
			}else{
				http_response_code(404);
				die('Need "mac_adr"');
			}
		}
		elseif(!(isset($_GET['mode']) && !empty($_GET['mode']))){
			http_response_code(404);
			die('Missing "mode", options are "register"');	
		}
		else{
			http_response_code(404);
			die('Not a valid mode, options are "register"');				
		}
		break;
	case 'DELETE':	// Delete
		// intentional fall through, deletion not allowed
	default:
		http_response_code(405);
		die('We were unable to understand your request.');
}
