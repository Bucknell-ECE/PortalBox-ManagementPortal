<?php

namespace Portalbox\Model;

use Portalbox\Enumeration\LoggedEventType;
use Portalbox\Exception\DatabaseException;
use PDO;

/**
 * Database bridge uses to determine the equipment type usage by a user or users
 */
class BadgeModel extends AbstractModel {
	/**
	 * Count logged events by equipment type for the specified user
	 *
	 * @throws DatabaseException  when the database can not be queried
	 * @return array  the map of equipment type id to count of uses for the user
	 */
	public function countForUser(int $user_id): array {
		$connection = $this->configuration()->readonly_db_connection();
		$sql = <<<EOQ
		SELECT
			COUNT(*) AS count,
			e.type_id AS equipment_type_id
		FROM log AS l
		INNER JOIN equipment AS e ON e.id = l.equipment_id
		INNER JOIN users_x_cards AS uxc ON uxc.card_id = l.card_id
		WHERE
			l.event_type_id = :event_type
			AND uxc.user_id = :user_id
		GROUP BY e.type_id
		EOQ;
		$query = $connection->prepare($sql);
		$query->bindValue(
			':event_type',
			LoggedEventType::DEAUTHENTICATION->value,
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

	/**
	 * Count logged events by equipment type for active users
	 *
	 * @throws DatabaseException  when the database can not be queried
	 * @return array  the list of equipment_type_id, user_id, and count records
	 *       as a hash/dictionary/object with those keys for all active users
	 */
	public function countForActiveUsers(): array {
		$connection = $this->configuration()->readonly_db_connection();
		$sql = <<<EOQ
		SELECT
			COUNT(*) AS count,
			e.type_id AS equipment_type_id,
			uxc.user_id,
			u.name,
			u.email
		FROM log AS l
		INNER JOIN equipment AS e ON e.id = l.equipment_id
		INNER JOIN users_x_cards AS uxc ON uxc.card_id = l.card_id
		INNER JOIN users AS u ON u.id = uxc.user_id
		WHERE
			l.event_type_id = :event_type
			AND u.is_active = true
		GROUP BY uxc.user_id, e.type_id;
		EOQ;
		$query = $connection->prepare($sql);
		$query->bindValue(
			':event_type',
			LoggedEventType::DEAUTHENTICATION->value,
			PDO::PARAM_INT
		);

		if (!$query->execute()) {
			throw new DatabaseException($connection->errorInfo()[2]);
		}

		$data = $query->fetchAll(PDO::FETCH_ASSOC);
		if (false === $data) {
			return [];
		}

		return $data;
	}
}
