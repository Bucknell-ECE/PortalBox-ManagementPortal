<?php

namespace Portalbox\Model;

use Portalbox\Exception\DatabaseException;
use Portalbox\Type\LoggedEventType;
use PDO;

/**
 * Database bridge uses to determine the equipment type usage by a user or users
 */
class BadgeModel extends AbstractModel {
	/**
	 * Count logged events by equipment type
	 *
	 * @throws DatabaseException  when the database can not be queried
	 * @return array
	 */
	public function countForUser(int $user_id): array {
		$connection = $this->configuration()->readonly_db_connection();
		$sql = <<<EOQ
		SELECT
			COUNT(*) AS count,
			et.id AS equipment_type_id
		FROM log AS l
		INNER JOIN equipment AS e ON e.id = l.equipment_id
		INNER JOIN equipment_types AS et ON et.id = e.type_id
		INNER JOIN users_x_cards AS uxc ON uxc.card_id = l.card_id
		WHERE
			l.event_type_id = :event_type
			AND uxc.user_id = :user_id
		GROUP BY et.id
		EOQ;
		$query = $connection->prepare($sql);
		$query->bindValue(
			':event_type',
			LoggedEventType::SUCCESSFUL_AUTHENTICATION,
			PDO::PARAM_INT
		);
		$query->bindValue(':user_id', $user_id, PDO::PARAM_INT);

		if (!$query->execute()) {
			throw new DatabaseException($connection->errorInfo()[2]);
		}

		$data = $query->fetchAll(PDO::FETCH_ASSOC);
		if (false === $data) {
			return [];
		}

		$counts = [];
		foreach ($data as $datum) {
			$counts[$datum['equipment_type_id']] = $datum['count'];
		}
		return $counts;
	}
}
