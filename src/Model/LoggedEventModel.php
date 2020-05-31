<?php

namespace Portalbox\Model;

use Portalbox\Entity\CardType;
use Portalbox\Entity\LoggedEvent;
use Portalbox\Model\Entity\LoggedEvent as PDOAwareLoggedEvent;
use Portalbox\Query\LoggedEventQuery;
use Portalbox\Exception\DatabaseException;

use PDO;

/**
 * LoggedEventModel is our bridge between the database and higher level Entities.
 * 
 * @package Portalbox\Model
 */
class LoggedEventModel extends AbstractModel {

	/**
	 * Read a logged event by its unique ID
	 *
	 * @param int id - the unique id of the loggedEvent
	 * @throws DatabaseException - when the database can not be queried
	 * @return LoggedEvent|null - the location or null if the location could not be found
	 */
	public function read(int $id) : ?LoggedEvent {
		$connection = $this->configuration()->readonly_db_connection();
		$sql = 'SELECT el.id, el.time, el.event_type_id, el.card_id, c.type_id AS card_type_id, el.equipment_id, e.name AS equipment_name, l.name AS location_name, u.id AS user_id, u.name as user_name FROM log AS el INNER JOIN equipment AS e ON el.equipment_id = e.id INNER JOIN locations AS l ON e.location_id = l.id INNER JOIN cards AS c ON el.card_id = c.id LEFT JOIN users_x_cards AS uxc ON el.card_id = uxc.card_id LEFT JOIN users AS u ON u.id = uxc.user_id WHERE el.id = :id';
		$query = $connection->prepare($sql);
		$query->bindValue(':id', $id, PDO::PARAM_INT);
		if($query->execute()) {
			if($data = $query->fetch(PDO::FETCH_ASSOC)) {
				return $this->buildLoggedEventFromArray($data);
			} else {
				return null;
			}
		} else {
			throw new DatabaseException($connection->errorInfo()[2]);
		}
	}

	/**
	 * Search for logged events
	 *
	 * @param LoggedEventQuery query - the search query to perform
	 * @throws DatabaseException - when the database can not be queried
	 * @return LoggedEvent[]|null - a list of logged events
	 */
	public function search(LoggedEventQuery $query) : ?array {
		if(NULL === $query) {
			// no query... bail
			return NULL;
		}

		$connection = $this->configuration()->readonly_db_connection();
		$sql = 'SELECT el.id, el.time, el.event_type_id, el.card_id, c.type_id AS card_type_id, el.equipment_id, e.name AS equipment_name, l.name AS location_name, u.id AS user_id, u.name as user_name FROM log AS el INNER JOIN equipment AS e ON el.equipment_id = e.id INNER JOIN locations AS l ON e.location_id = l.id INNER JOIN cards AS c ON el.card_id = c.id LEFT JOIN users_x_cards AS uxc ON el.card_id = uxc.card_id LEFT JOIN users AS u ON u.id = uxc.user_id';

		$where_clause_fragments = array();
		$parameters = array();
		if($query->equipment_id()) {
			$where_clause_elements[] = 'l.equipment_id = :equipment_id';
			$parameters[':equipment_id'] = $query->equipment_id();
		}
		if($query->location_id()) {
			$where_clause_elements[] = 'e.location_id = :location_id';
			$parameters[':location_id'] = $query->location_id();
		}
		if($query->type_id()) {
			$where_clause_elements[] = 'e.event_type_id = :event_type_id';
			$parameters[':event_type_id'] = $query->type_id();
		}
		if($query->on_or_after()) {
			$where_clause_elements[] = 'l.time >= :after';
			$parameters[':after'] = $query->on_or_after();
		}
		if($query->on_or_before()) {
			$where_clause_elements[] = 'l.time <= :before';
			$parameters[':before'] = $query->on_or_before();
		}
		if(0 < count($where_clause_fragments)) {
			$sql .= ' WHERE ';
			$sql .= join(' AND ', $where_clause_fragments);
		}
		$sql .= ' ORDER BY l.time DESC';

		$statement = $connection->prepare($sql);
		// run search
		foreach($parameters as $k => $v) {
			$query->bindValue($k, $v);
		}
		
		if($statement->execute()) {
			$data = $statement->fetchAll(PDO::FETCH_ASSOC);
			if(FALSE !== $data) {
				return $this->buildLoggedEventsFromArray($data);
			} else {
				return null;
			}
		} else {
			throw new DatabaseException($connection->errorInfo()[2]);
		}
	}

	private function buildLoggedEventFromArray(array $data) : LoggedEvent {
		// u.id AS user_id, u.name as user_name
		return (new PDOAwareLoggedEvent($this->configuration()))
					->set_id($data['id'])
					->set_type_id($data['event_type_id'])
					->card_id($data['card_id'])
					->card_type_id($data['card_type_id'])
					->set_equipment_id($data['equipment_id'])
					->set_equipment_name($data['equipment_name'])
					//->set_location_name($data['location_name'])
					->set_time($data['time'])
					->set_user_name($data['user_name']);
	}

	private function buildLoggedEventsFromArray(array $data) : array {
		$log = array();

		foreach($data as $datum) {
			$log[] = $this->buildLoggedEventFromArray($datum);
		}

		return $log;
	}
}