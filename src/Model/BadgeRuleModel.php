<?php

namespace Portalbox\Model;

use PDO;
use Portalbox\Exception\DatabaseException;
use Portalbox\Type\BadgeLevel;
use Portalbox\Type\BadgeRule;

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

		$sql = 'INSERT INTO badge_rule_levels (badge_rule_id, name, uses) VALUES (:badge_rule_id, :name, :uses)';
		$statement = $connection->prepare($sql);

		foreach ($rule->levels() as $level) {
			$statement->bindValue(':badge_rule_id', $badge_rule_id, PDO::PARAM_INT);
			$statement->bindValue(':name', $level->name());
			$statement->bindValue(':uses', $level->uses(), PDO::PARAM_INT);

			if (!$statement->execute()) {
				$connection->rollBack();
				throw new DatabaseException($statement->errorInfo()[2]);
			}

			$level
				->set_id($connection->lastInsertId('badge_rule_levels_id_seq'))
				->set_badge_rule_id($badge_rule_id);
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

		$sql = 'SELECT * FROM badge_rule_levels WHERE badge_rule_id = :badge_rule_id';
		$statement = $connection->prepare($sql);

		$statement->bindValue(':badge_rule_id', $id, PDO::PARAM_INT);

		if (!$statement->execute()) {
			throw new DatabaseException($connection->errorInfo()[2]);
		}

		$data = $statement->fetchAll(PDO::FETCH_ASSOC);
		if (false !== $data) {
			$badge_rule->set_levels(array_map(
				fn ($datum) => $this->buildBadgeRuleLevelFromArray($datum),
				$data
			));
		}

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

		// Again we don't really care about level ids so we'll delete and
		// recreate as needed
		$sql = 'DELETE FROM badge_rule_levels WHERE badge_rule_id = :id';
		$statement = $connection->prepare($sql);

		$statement->bindValue(':id', $id, PDO::PARAM_INT);

		if (!$statement->execute()) {
			$connection->rollBack();
			throw new DatabaseException($statement->errorInfo()[2]);
		}

		$sql = 'INSERT INTO badge_rule_levels (badge_rule_id, name, uses) VALUES (:badge_rule_id, :name, :uses)';
		$statement = $connection->prepare($sql);

		foreach ($rule->levels() as $level) {
			$statement->bindValue(':badge_rule_id', $id, PDO::PARAM_INT);
			$statement->bindValue(':name', $level->name());
			$statement->bindValue(':uses', $level->uses(), PDO::PARAM_INT);

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

		$rules = array_map(
			fn ($datum) => $this->buildBadgeRuleFromArray($datum),
			$data
		);

		$level_map = [];
		$equipment_type_map = [];
		foreach ($rules as $rule) {
			$id = $rule->id();
			$level_map[$id] = [];
			$equipment_type_map[$id] = [];
		}

		// get badge levels for the badge rules
		$sql = 'SELECT * FROM badge_rule_levels';
		$statement = $connection->prepare($sql);

		if (!$statement->execute()) {
			throw new DatabaseException($connection->errorInfo()[2]);
		}

		$data = $statement->fetchAll(PDO::FETCH_ASSOC);
		if (false === $data) {
			throw new DatabaseException('Unable to read badge levels from result set');
		}

		foreach ($data as $datum) {
			$level = $this->buildBadgeRuleLevelFromArray($datum);
			$badge_rule_id = $level->badge_rule_id();
			if (array_key_exists($badge_rule_id, $level_map)) {
				$level_map[$badge_rule_id][] = $level;
			}
		}

		// get equipment types for the badge rules
		$sql = 'SELECT * FROM badge_rules_x_equipment_types';
		$statement = $connection->prepare($sql);

		if (!$statement->execute()) {
			throw new DatabaseException($connection->errorInfo()[2]);
		}

		$data = $statement->fetchAll(PDO::FETCH_ASSOC);
		if (false === $data) {
			throw new DatabaseException('Unable to read equipment types from result set');
		}

		foreach ($data as $datum) {
			$badge_rule_id = $datum['badge_rule_id'];
			if (array_key_exists($badge_rule_id, $equipment_type_map)) {
				$equipment_type_map[$badge_rule_id][] = $datum['equipment_type_id'];
			}
		}

		// merge data together
		foreach ($rules as $rule) {
			$id = $rule->id();

			$levels = $level_map[$id];
			usort($levels, fn($a, $b) => $a->uses() - $b->uses());

			$rule
				->set_levels($levels)
				->set_equipment_type_ids($equipment_type_map[$id]);
		}

		return $rules;
	}

	private function buildBadgeRuleFromArray(array $data): BadgeRule {
		return (new BadgeRule())
			->set_id($data['id'])
			->set_name($data['name']);
	}

	private function buildBadgeRuleLevelFromArray(array $data): BadgeLevel {
		return (new BadgeLevel())
			->set_id($data['id'])
			->set_badge_rule_id($data['badge_rule_id'])
			->set_name($data['name'])
			->set_uses($data['uses']);
	}
}
