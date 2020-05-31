<?php


require '../../src/autoload.php';

use Portalbox\Config;
use Portalbox\ResponseHandler;
use Portalbox\Session;

use Portalbox\Entity\Permission;

use Portalbox\Model\LoggedEventModel;

use Portalbox\Query\LoggedEventQuery;

use Portalbox\Transform\LoggedEventTransformer;

// switch on the request method
switch($_SERVER['REQUEST_METHOD']) {
	case 'GET':		// List
		// check authorization
		Session::require_authorization(Permission::LIST_LOGS);

		try {
			$model = new LoggedEventModel(Config::config());
			$query = new LoggedEventQuery();
			if(isset($_GET['equipment_id']) && !empty($_GET['equipment_id'])) {
				$query->set_equipment_id($_GET['equipment_id']);
			}
			if(isset($_GET['location_id']) && !empty($_GET['location_id'])) {
				$query->set_location_id($_GET['location_id']);
			}
			if(isset($_GET['type_id']) && !empty($_GET['type_id'])) {
				$query->set_type_id($_GET['type_id']);
			}
			if(isset($_GET['after']) && !empty($_GET['after'])) {
				$query->set_on_or_after($_GET['after']);
			}
			if(isset($_GET['before']) && !empty($_GET['before'])) {
				$query->set_on_or_before($_GET['before']);
			}

			$log = $model->search($query);
			$transformer = new LoggedEventTransformer();
			ResponseHandler::render($log, $transformer);
		} catch(Exception $e) {
			http_response_code(500);
			die('We experienced issues communicating with the database');
		}

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
			http_response_code(400);
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
			switch($_SERVER['HTTP_ACCEPT']) {
				case 'text/csv':
					$out = fopen('php://output', 'w');
					fputcsv($out, array('id', 'time', 'event type', 'equipment id', 'equipment', 'card id', 'user'));
					foreach($events as $record) {
						fputcsv($out, $record);
					}
					fclose($out);
					break;
				case 'application/json':
				default:
					render_json($events);
			}
		} else {
			http_response_code(500);
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
		http_response_code(405);
		die('We were unable to understand your request.');
}
