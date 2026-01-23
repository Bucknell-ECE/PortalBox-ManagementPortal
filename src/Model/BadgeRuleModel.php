<?php

namespace Portalbox\Model;

use Portalbox\Entity\BadgeRule;
use Portalbox\Exception\DatabaseException;
use PDO;

/**
 * Adapter for storing and retrieving badge configurations from the database.
 */
class BadgeRuleModel extends AbstractModel {
	/**
	 * Save a new badge rule in the database
	 *
	 * @param BadgeRule rule - the badge rule to save to the database
	 * @throws DatabaseException - when the rule can not be saved to the database
	 * @return BadgeRule - the configuration as persisted in the database
	 */
	public function create(BadgeRule $rule): BadgeRule {
		$connection = $this->configuration()->writable_db_connection();
		$sql = 'INSERT INTO badge_rules (name) VALUES (:name)';
		$statement = $connection->prepare($sql);

		$statement->bindValue(':name', $rule->name());

		if (!$connection->beginTransaction()) {
			throw new DatabaseException($connection->errorInfo()[2]);
		}

		if (!$statement->execute()) {
			$connection->rollBack();	// This is unlikely to succeed but
										// it balances the beginTransaction
			throw new DatabaseException($connection->errorInfo()[2]);
		}

		$badge_rule_id = $connection->lastInsertId('badge_rules_id_seq');

		$sql = 'INSERT INTO badge_rules_x_equipment_types (badge_rule_id, equipment_type_id) VALUES (:badge_rule_id, :equipment_type_id)';
		$statement = $connection->prepare($sql);

		foreach ($rule->equipment_type_ids() as $equipment_type_id) {
			$statement->bindValue(':badge_rule_id', $badge_rule_id, PDO::PARAM_INT);
			$statement->bindValue(':equipment_type_id', $equipment_type_id, PDO::PARAM_INT);

			if (!$statement->execute()) {
				$connection->rollBack();
				throw new DatabaseException($statement->errorInfo()[2]);
			}
		}

		$connection->commit();
		return $rule->set_id($badge_rule_id);
	}

	/**
	 * Read a badge rule by its unique id
	 *
	 * @param int id - the unique id of the badge rule
	 * @throws DatabaseException - when the database can not be queried
	 * @return BadgeRule|null - the badge rule or null if the rule could not be found
	 */
	public function read(int $id): ?BadgeRule {
		$connection = $this->configuration()->readonly_db_connection();
		$sql = 'SELECT id, name FROM badge_rules WHERE id = :id';
		$statement = $connection->prepare($sql);

		$statement->bindValue(':id', $id, PDO::PARAM_INT);

		if (!$statement->execute()) {
			throw new DatabaseException($connection->errorInfo()[2]);
		}

		$data = $statement->fetch(PDO::FETCH_ASSOC);
		if ($data === false) {
			return null;
		}

		$badge_rule = $this->buildBadgeRuleFromArray($data);

		$sql = <<<EOQ
		SELECT
			et.id, et.name
		FROM
			equipment_types AS et
		INNER JOIN
			badge_rules_x_equipment_types AS bret ON et.id = bret.equipment_type_id
		WHERE
			badge_rule_id = :badge_rule_id
		EOQ;
		$statement = $connection->prepare($sql);

		$statement->bindValue(':badge_rule_id', $id, PDO::PARAM_INT);

		if (!$statement->execute()) {
			throw new DatabaseException($connection->errorInfo()[2]);
		}

		return $badge_rule->set_equipment_type_ids($statement->fetchAll(PDO::FETCH_COLUMN));
	}

	/**
	 * Save a modified badge rule to the database
	 *
	 * @param BadgeRule rule - the badge rule to save to the database
	 * @throws DatabaseException - when the database can not be queried
	 * @return BadgeRule|null - the badge rule or null if the rule could not be saved
	 */
	public function update(BadgeRule $rule): ?BadgeRule {
		$id = $rule->id();

		if ($this->read($id) === null) {
			return null;
		}

		$connection = $this->configuration()->writable_db_connection();
		$sql = 'UPDATE badge_rules SET name = :name WHERE id = :id';
		$statement = $connection->prepare($sql);

		$statement->bindValue(':id', $id, PDO::PARAM_INT);
		$statement->bindValue(':name', $rule->name());

		
		if (!$connection->beginTransaction()) {
			throw new DatabaseException($connection->errorInfo()[2]);
		}

		if (!$statement->execute()) {
			$connection->rollBack();	// This is unlikely to succeed but
										// it balances the beginTransaction
			throw new DatabaseException($connection->errorInfo()[2]);
		}

		// We don't care about the row ids of the equipment type mappings so
		// the easy way to update is to delete and recreate them
		$sql = 'DELETE FROM badge_rules_x_equipment_types WHERE badge_rule_id = :id';
		$statement = $connection->prepare($sql);

		$statement->bindValue(':id', $id, PDO::PARAM_INT);
		if (!$statement->execute()) {
			$connection->rollBack();
			throw new DatabaseException($statement->errorInfo()[2]);
		}

		$sql = 'INSERT INTO badge_rules_x_equipment_types (badge_rule_id, equipment_type_id) VALUES (:badge_rule_id, :equipment_type_id)';
		$statement = $connection->prepare($sql);

		foreach ($rule->equipment_type_ids() as $equipment_type_id) {
			$statement->bindValue(':badge_rule_id', $id, PDO::PARAM_INT);
			$statement->bindValue(':equipment_type_id', $equipment_type_id, PDO::PARAM_INT);

			if (!$statement->execute()) {
				$connection->rollBack();
				throw new DatabaseException($statement->errorInfo()[2]);
			}
		}

		$connection->commit();

		return $this->read($id);
	}

	/**
	 * Delete a badge rule  specified by its unique id
	 *
	 * @param int id - the unique id of the badge rule 
	 * @throws DatabaseException - when the database can not be queried
	 * @return BadgeRule|null - the badge rule or null if the rule could not be found
	 */
	public function delete(int $id): ?BadgeRule {
		$rule = $this->read($id);

		if ($rule === null) {
			return null;
		}

		$connection = $this->configuration()->writable_db_connection();
		$sql = 'DELETE FROM badge_rules WHERE id = :id';
		$statement = $connection->prepare($sql);

		$statement->bindValue(':id', $id, PDO::PARAM_INT);

		if (!$statement->execute()) {
			throw new DatabaseException($connection->errorInfo()[2]);
		}

		return $rule;
	}

	/**
	 * Get the list of all badges
	 *
	 * @throws DatabaseException - when the database can not be queried
	 * @return BadgeRule[] - the list of badge rules
	 */
	public function search(): ?array {
		$connection = $this->configuration()->readonly_db_connection();
		$sql = 'SELECT id, name FROM badge_rules';

		$statement = $connection->prepare($sql);

		if (!$statement->execute()) {
			throw new DatabaseException($connection->errorInfo()[2]);
		}

		$data = $statement->fetchAll(PDO::FETCH_ASSOC);
		if (false === $data) {
			return [];
		}

		return array_map(
			fn ($datum) => $this->buildBadgeRuleFromArray($datum),
			$data
		);
	}

	private function buildBadgeRuleFromArray(array $data): BadgeRule {
		return (new BadgeRule())
			->set_id($data['id'])
			->set_name($data['name']);
	}
}
