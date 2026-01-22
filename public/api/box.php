<?php

require '../../src/autoload.php';

use Portalbox\Config;
use Portalbox\ResponseHandler;
use Portalbox\Entity\CardType;
use Portalbox\Entity\Permission;
use Portalbox\Model\EquipmentModel;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Model\LocationModel;
use Portalbox\Exception\DatabaseException;
use Portalbox\Transform\EquipmentTransformer;
use Portalbox\Query\CardQuery;
use Portalbox\Session\Session;

$session = new Session();

// switch on the request method
switch ($_SERVER['REQUEST_METHOD']) {
	case 'GET':
		$session->require_authorization(Permission::READ_EQUIPMENT);

		$path = realpath(__DIR__ . DIRECTORY_SEPARATOR . '../../config/config.ini');
		$settings = parse_ini_file($path, TRUE);
		$dsn = 'host=' . $settings['database']['host'] . ';dbname=' . $settings['database']['database'];
		$connection = new PDO($settings['database']['driver'] . ':' . $dsn, $settings['database']['username'], $settings['database']['password']);

		if ($_GET['mode'] == "get_card_details") {
			if ((isset($_GET['card_id']) && !empty($_GET['card_id'])) && (isset($_GET['equipment_id']) && !empty($_GET['equipment_id']))) {
				// Get user details
				$card_id = $_GET['card_id'];
				$e_id = $_GET['equipment_id'];

				// What is the card type?
				$sql = 'SELECT type_id FROM cards WHERE id = :card_id';
				$query = $connection->prepare($sql);
				$query->bindValue(':card_id', $card_id);
				if (!$query->execute()) {
					http_response_code(500);
					die("Database Exception: " . $query->errorInfo()[2]);
				}

				$data = $query->fetch(PDO::FETCH_NUM);
				if ($data === false) {
					// card not found; report card as invalid
					die(json_encode([[
						'user_auth' => 0,
						'user_balance' => 0.0,
						'user_active' => 0,
						'card_type' => -1,
						'user_role' => 0
					]]));
				}

				$card_type = $data[0];
				if ($card_type !== CardType::USER) {
					die(json_encode([[
						'user_auth' => 0,
						'user_balance' => 0.0,
						'user_active' => 0,
						'card_type' => $card_type,
						'user_role' => 0
					]]));
				}

				// Get the user associated with the card
				$sql = 'SELECT user_id FROM users_x_cards as uxc WHERE card_id = :card_id';
				$query = $connection->prepare($sql);
				$query->bindValue(':card_id', $card_id);

				if (!$query->execute()) {
					http_response_code(500);
					die("Database Exception: " . $query->errorInfo()[2]);
				}

				$user_id = $query->fetchColumn();
				if (!$user_id) {
					http_response_code(500);
					die('Invalid User Card');
				}

				// Is the user authorized for the equipment?
				$sql = 'SELECT COUNT(*) FROM authorizations WHERE user_id = :user_id AND equipment_type_id = :e_id';
				$query = $connection->prepare($sql);
				$query->bindValue(':user_id', $user_id);
				$query->bindValue(':e_id', $e_id);

				if (!$query->execute()) {
					http_response_code(500);
					die("Database Exception: " . $query->errorInfo()[2]);
				}

				$user_auth = $query->fetchColumn() === 1;

				// What is the user's balance
				$sql = 'SELECT get_user_balance_for_card(:card_id)';
				$query = $connection->prepare($sql);
				$query->bindValue(':card_id', $card_id);

				if (!$query->execute()) {
					http_response_code(500);
					die("Database Exception: " . $query->errorInfo()[2]);
				}

				$user_balance = $query->fetch(PDO::FETCH_NUM)[0];

				// Get user info
				$sql = 'SELECT is_active, role_id FROM users WHERE id = :user_id';
				$query = $connection->prepare($sql);
				$query->bindValue(':user_id', $user_id);

				if (!$query->execute()) {
					http_response_code(500);
					die("Database Exception: " . $query->errorInfo()[2]);
				}

				list($user_active, $user_role) = $query->fetch(PDO::FETCH_NUM);

				die(json_encode([[
					'user_auth' => (int)$user_auth,
					'user_balance' => $user_balance,
					'user_active' => $user_active,
					'card_type' => $card_type,
					'user_role' => $user_role
				]]));
			} else {
				http_response_code(404);
				die('Needs "card_id" and "equipment_id"');
			}
		} elseif ($_GET['mode'] == "check_reg") {
			if (isset($_GET['mac_adr']) && !empty($_GET['mac_adr'])) {
				$mac_adr = $_GET['mac_adr'];

				// Check if the box is registered
				$sql = 'SELECT count(id) FROM equipment WHERE mac_address = :mac_adr';
				$query = $connection->prepare($sql);
				$query->bindValue(':mac_adr', $mac_adr);
				if (!$query->execute()) {
					http_response_code(500);
					die("Database Exception: " . $query->errorInfo()[2]);
				}

				$registered = $query->fetch(PDO::FETCH_NUM)[0];
				echo $registered;
			} else {
				http_response_code(404);
				die('Need Mac address');
			}
		} elseif ($_GET['mode'] == "get_profile") {
			if (isset($_GET['mac_adr']) && !empty($_GET['mac_adr'])) {
				$mac_adr = $_GET['mac_adr'];
				// Check if the box is registered
				$sql = <<<EOQ
				SELECT
					e.id,
					e.type_id,
					t.name,
					e.location_id,
					l.name,
					e.timeout,
					t.allow_proxy,
					t.requires_training,
					t.charge_policy_id > 2 AS "charge_policy"
				FROM equipment AS e
				INNER JOIN equipment_types AS t ON e.type_id = t.id
				INNER JOIN locations AS l ON e.location_id =  l.id
				WHERE e.mac_address = :mac_adr
				EOQ;
				$query = $connection->prepare($sql);
				$query->bindValue(':mac_adr', $mac_adr);
				if (!$query->execute()) {
					http_response_code(500);
					die("Database Exception: " . $query->errorInfo()[2]);
				}

				$profile = $query->fetch(PDO::FETCH_NAMED);
				if (is_array($profile)) {
					ResponseHandler::render([$profile]);
				} else {
					http_response_code(404);
					die('Box not found');
				}
			} else {
				http_response_code(404);
				die('Need "mac_adr"');
			}
		} elseif ($_GET['mode'] == "get_equipment_name") {
			if (isset($_GET['equipment_id']) && !empty($_GET['equipment_id'])) {
				// Check if the box is registered
				$sql = 'SELECT name FROM equipment WHERE id = :equipment_id';
				$query = $connection->prepare($sql);
				$query->bindValue(':equipment_id', $_GET['equipment_id']);
				if (!$query->execute()) {
					http_response_code(500);
					die("Database Exception: " . $query->errorInfo()[2]);
				}

				$profile = $query->fetch(PDO::FETCH_ASSOC);
				ResponseHandler::render([$profile]);
			} else {
				http_response_code(404);
				die('Need "equipment_id"');
			}
		} elseif ($_GET['mode'] == "get_user") {
			if (isset($_GET['card_id']) && !empty($_GET['card_id'])) {
				// Check if the box is registered
				$sql = 'SELECT u.name, u.email FROM users_x_cards AS c JOIN users AS u ON u.id = c.user_id WHERE c.card_id = :card_id';
				$query = $connection->prepare($sql);
				$query->bindValue(':card_id', $_GET['card_id']);
				if (!$query->execute()) {
					http_response_code(500);
					die("Database Exception: " . $query->errorInfo()[2]);
				}

				$profile = $query->fetch(PDO::FETCH_ASSOC);
				ResponseHandler::render([$profile]);
			} else {
				http_response_code(404);
				die('Need "equipment_id"');
			}
		} elseif (!(isset($_GET['mode']) && !empty($_GET['mode']))) {
			http_response_code(404);
			die('Missing "mode", options are "get_card_details", "check_reg", "get_equipment_name", and "get_profile"');
		} else {
			http_response_code(404);
			die('Not a valid mode, options are "get_card_details", "check_reg", "get_equipment_name" and "get_profile"');
		}
		break;
	case 'POST':	// Update
		$session->require_authorization(Permission::READ_EQUIPMENT);

		$path = realpath(__DIR__ . DIRECTORY_SEPARATOR . '../../config/config.ini');
		$settings = parse_ini_file($path, TRUE);
		$dsn = 'host=' . $settings['database']['host'] . ';dbname=' . $settings['database']['database'];
		$connection = new PDO($settings['database']['driver'] . ':' . $dsn, $settings['database']['username'], $settings['database']['password']);

		if ($_GET['mode'] == "log_access_attempt") {
			if (!isset($_GET['successful'])) {
				http_response_code(404);
				die('missing params needs "successful", "card_id", and "equipment_id". Failed at "successful"');
			}
			if (!(isset($_GET['card_id']) && !empty($_GET['card_id']))) {
				http_response_code(404);
				die('missing params needs "successful", "card_id", and "equipment_id". Failed at "card_id"');
			}
			if (!(isset($_GET['equipment_id']) && !empty($_GET['equipment_id']))) {
				http_response_code(404);
				die('missing params needs "successful", "card_id", and "equipment_id". Failed at "equipment_id"');
			}

			$sql = "CALL log_access_attempt(:successful, :card_id, :equipment_id)";
			$query = $connection->prepare($sql);
			$query->bindValue(':successful', $_GET['successful']);
			$query->bindValue(':card_id', $_GET['card_id']);
			$query->bindValue(':equipment_id', $_GET['equipment_id']);
			if (!$query->execute()) {
				http_response_code(500);
				die("Database Exception: " . $query->errorInfo()[2]);
			}

			echo "completed successfully";
		} elseif ($_GET['mode'] == "log_access_completion") {
			if (!(isset($_GET['card_id']) && !empty($_GET['card_id']))) {
				http_response_code(404);
				die('missing params needs "card_id", and "equipment_id". Failed at "card_id"');
			}
			if (!(isset($_GET['equipment_id']) && !empty($_GET['equipment_id']))) {
				http_response_code(404);
				die('missing params needs "equipment_id", and "equipment_id". Failed at "equipment_id"');
			}

			$sql = "CALL log_access_completion(:card_id, :equipment_id)";
			$query = $connection->prepare($sql);
			$query->bindValue(':card_id', $_GET['card_id']);
			$query->bindValue(':equipment_id', $_GET['equipment_id']);
			if (!$query->execute()) {
				http_response_code(500);
				die("Database Exception: " . $query->errorInfo()[2]);
			}

			echo "completed successfully";
		} elseif ($_GET['mode'] == "log_shutdown_status") {
			if (!(isset($_GET['card_id']) && !empty($_GET['card_id']))) {
				http_response_code(404);
				die('missing params needs "card_id", and "equipment_id". Failed at "card_id"');
			}
			if (!(isset($_GET['equipment_id']) && !empty($_GET['equipment_id']))) {
				http_response_code(404);
				die('missing params needs "card_id", and "equipment_id". Failed at "equipment_id"');
			}

			$sql = 'INSERT INTO log(event_type_id, equipment_id, card_id)
					(SELECT id, :equipment_id, :card_id FROM event_types
					WHERE name = "Planned Shutdown")';
			$query = $connection->prepare($sql);
			$query->bindValue(':equipment_id', $_GET['equipment_id']);
			$query->bindValue(':card_id', $_GET['card_id']);

			if (!$query->execute()) {
				http_response_code(500);
				die("Database Exception: " . $query->errorInfo()[2]);
			}

			echo "completed successfully";
		} elseif ($_GET['mode'] == "log_started_status") {
			if (!(isset($_GET['equipment_id']) && !empty($_GET['equipment_id']))) {
				http_response_code(404);
				die('missing params needs "equipment_id". Failed at "equipment_id"');
			}

			$sql = 'INSERT INTO log(event_type_id, equipment_id)
					(SELECT id, :equipment_id FROM event_types
					WHERE name = "Startup Complete")';
			$query = $connection->prepare($sql);
			$query->bindValue(':equipment_id', $_GET['equipment_id']);

			if (!$query->execute()) {
				http_response_code(500);
				die("Database Exception: " . $query->errorInfo()[2]);
			}

			echo "Completed successfully";
		} elseif ($_GET['mode'] == "record_ip") {
			if (!(isset($_GET['equipment_id']) && !empty($_GET['equipment_id']))) {
				http_response_code(404);
				die('missing params needs "equipment_id". Failed at "equipment_id"');
			}
			if (!(isset($_GET['ip_address']) && !empty($_GET['ip_address']))) {
				http_response_code(404);
				die('missing params needs "ip_address". Failed at "ip_address"');
			}

			$sql = 'UPDATE equipment
					SET ip_address = :ip_address
					WHERE id = :equipment_id';
			$query = $connection->prepare($sql);
			$query->bindValue(':equipment_id', $_GET['equipment_id']);
			$query->bindValue(':ip_address', $_GET['ip_address']);

			if (!$query->execute()) {
				http_response_code(500);
				die("Database Exception: " . $query->errorInfo()[2]);
			}

			echo "Completed successfully";
		} elseif (!(isset($_GET['mode']) && !empty($_GET['mode']))) {
			http_response_code(404);
			die('Missing "mode", options are "log_access_attempt", "log_access_completion", "log_shutdown_status", "log_started_status", and "record_ip"');
		} else {
			http_response_code(404);
			die('Not a valid mode, options are "log_access_attempt", "log_access_completion", "log_shutdown_status", "log_started_status", and "record_ip"');
		}
		break;
	case 'PUT':		// Create
		$session->require_authorization(Permission::READ_EQUIPMENT);

		$path = realpath(__DIR__ . DIRECTORY_SEPARATOR . '../../config/config.ini');
		$settings = parse_ini_file($path, TRUE);
		$dsn = 'host=' . $settings['database']['host'] . ';dbname=' . $settings['database']['database'];
		$connection = new PDO($settings['database']['driver'] . ':' . $dsn, $settings['database']['username'], $settings['database']['password']);

		if ($_GET['mode'] == "register") {
			if (isset($_GET['mac_adr']) && !empty($_GET['mac_adr'])) {
				$mac_adr = $_GET['mac_adr'];
				// Check if the box is registered
				$sql = 'INSERT INTO equipment (name, type_id, mac_address, location_id) VALUES ("New Portal Box", 1, :mac_adr, 1)';
				$query = $connection->prepare($sql);
				$query->bindValue(':mac_adr', $mac_adr);
				if (!$query->execute()) {
					http_response_code(500);
					die("Database Exception: " . $query->errorInfo()[2]);
				}

				echo "Completed successfully";
			} else {
				http_response_code(404);
				die('Need "mac_adr"');
			}
		} elseif (!(isset($_GET['mode']) && !empty($_GET['mode']))) {
			http_response_code(404);
			die('Missing "mode", options are "register"');
		} else {
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
