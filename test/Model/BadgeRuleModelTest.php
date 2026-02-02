<?php

declare(strict_types=1);

namespace Test\Portalbox\Model;

use PHPUnit\Framework\TestCase;
use Portalbox\Config;
use Portalbox\Model\BadgeRuleModel;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Type\BadgeLevel;
use Portalbox\Type\BadgeRule;
use Portalbox\Type\ChargePolicy;
use Portalbox\Type\EquipmentType;

final class BadgeRuleModelTest extends TestCase {
	public function testCreateReadUpdateDelete(): void {
		$badge_rule_model = new BadgeRuleModel(Config::config());
		$equipment_type_model = new EquipmentTypeModel(Config::config());

		$equipment_type_id1 = $equipment_type_model->create(
			(new EquipmentType())
				->set_name('Miller 211 MIG Welder')
				->set_requires_training(true)
				->set_charge_rate('0.01')
				->set_charge_policy_id(ChargePolicy::PER_MINUTE)
				->set_allow_proxy(false)
		)->id();

		$equipment_type_id2 = $equipment_type_model->create(
			(new EquipmentType())
				->set_name('Miller 213 MIG Welder')
				->set_requires_training(true)
				->set_charge_rate('0.01')
				->set_charge_policy_id(ChargePolicy::PER_MINUTE)
				->set_allow_proxy(false)
		)->id();

		$equipment_type_id3 = $equipment_type_model->create(
			(new EquipmentType())
				->set_name('Miller 215 MIG Welder')
				->set_requires_training(true)
				->set_charge_rate('0.01')
				->set_charge_policy_id(ChargePolicy::PER_MINUTE)
				->set_allow_proxy(false)
		)->id();

		$name = 'Welder';
		$level1_name = 'Novice Welder';
		$level2_name = 'Expert Welder';
		$level1_uses = 5;
		$level2_uses = 10;
		$equipment_type_ids = [
			$equipment_type_id1,
			$equipment_type_id2
		];
		$level1 = (new BadgeLevel())
			->set_name($level1_name)
			->set_uses($level1_uses);
		$levels = [
			(new BadgeLevel())
				->set_name($level2_name)
				->set_uses($level2_uses),
			$level1
		];

		$rule = $badge_rule_model->create(
			(new BadgeRule())
				->set_name($name)
				->set_equipment_type_ids($equipment_type_ids)
				->set_levels($levels)
		);

		self::assertInstanceOf(BadgeRule::class, $rule);
		$id = $rule->id();
		self::assertIsInt($id);
		self::assertSame($name, $rule->name());
		self::assertSame($equipment_type_ids, $rule->equipment_type_ids());
		$level_names = [];
		foreach($rule->levels() as $level) {
			$level_id = $level->id();
			self::assertIsInt($level_id);
			self::assertGreaterThan(0, $level_id);
			self::assertSame($id, $level->badge_rule_id());
			switch($level->name()) {
				case $level1_name:
					self::assertSame($level1_uses, $level->uses());
					break;
				case $level2_name:
					self::assertSame($level2_uses, $level->uses());
					break;
			}
			$level_names[] = $level->name();
		}
		self::assertEqualsCanonicalizing(
			$level_names,
			[
				$level1_name,
				$level2_name
			]
		);

		$rule = $badge_rule_model->read($id);

		self::assertInstanceOf(BadgeRule::class, $rule);
		self::assertSame($id, $rule->id());
		self::assertSame($name, $rule->name());
		self::assertSame($equipment_type_ids, $rule->equipment_type_ids());
		$level_names = [];
		foreach($rule->levels() as $level) {
			$level_id = $level->id();
			self::assertIsInt($level_id);
			self::assertGreaterThan(0, $level_id);
			self::assertSame($id, $level->badge_rule_id());
			switch($level->name()) {
				case $level1_name:
					self::assertSame($level1_uses, $level->uses());
					break;
				case $level2_name:
					self::assertSame($level2_uses, $level->uses());
					break;
			}
			$level_names[] = $level->name();
		}
		self::assertEqualsCanonicalizing(
			$level_names,
			[
				$level1_name,
				$level2_name
			]
		);

		$name = 'Welding Pro';
		$level2_name = 'Journeyman Welder';
		$level3_name = 'Professional Welder';
		$level2_uses = 25;
		$level3_uses = 150;
		$equipment_type_ids = [
			$equipment_type_id1,
			$equipment_type_id3
		];
		$levels = [
			(new BadgeLevel())
				->set_name($level2_name)
				->set_uses($level2_uses),
			$level1,
			(new BadgeLevel())
				->set_name($level3_name)
				->set_uses($level3_uses),
		];

		$rule = $badge_rule_model->update(
			(new BadgeRule())
				->set_id($id)
				->set_name($name)
				->set_equipment_type_ids($equipment_type_ids)
				->set_levels($levels)
		);

		self::assertInstanceOf(BadgeRule::class, $rule);
		self::assertSame($id, $rule->id());
		self::assertSame($name, $rule->name());
		self::assertSame($equipment_type_ids, $rule->equipment_type_ids());
		$level_names = [];
		foreach($rule->levels() as $level) {
			$level_id = $level->id();
			self::assertIsInt($level_id);
			self::assertGreaterThan(0, $level_id);
			self::assertSame($id, $level->badge_rule_id());
			switch($level->name()) {
				case $level1_name:
					self::assertSame($level1_uses, $level->uses());
					break;
				case $level2_name:
					self::assertSame($level2_uses, $level->uses());
					break;
				case $level3_name:
					self::assertSame($level3_uses, $level->uses());
					break;
			}
			$level_names[] = $level->name();
		}
		self::assertEqualsCanonicalizing(
			$level_names,
			[
				$level1_name,
				$level2_name,
				$level3_name
			]
		);

		$rule = $badge_rule_model->read($id);

		self::assertInstanceOf(BadgeRule::class, $rule);
		self::assertSame($id, $rule->id());
		self::assertSame($name, $rule->name());
		self::assertSame($equipment_type_ids, $rule->equipment_type_ids());
		$level_names = [];
		foreach($rule->levels() as $level) {
			$level_id = $level->id();
			self::assertIsInt($level_id);
			self::assertGreaterThan(0, $level_id);
			self::assertSame($id, $level->badge_rule_id());
			switch($level->name()) {
				case $level1_name:
					self::assertSame($level1_uses, $level->uses());
					break;
				case $level2_name:
					self::assertSame($level2_uses, $level->uses());
					break;
				case $level3_name:
					self::assertSame($level3_uses, $level->uses());
					break;
			}
			$level_names[] = $level->name();
		}
		self::assertEqualsCanonicalizing(
			$level_names,
			[
				$level1_name,
				$level2_name,
				$level3_name
			]
		);

		$rule = $badge_rule_model->delete($id);

		self::assertInstanceOf(BadgeRule::class, $rule);
		self::assertSame($id, $rule->id());
		self::assertSame($name, $rule->name());
		self::assertSame($equipment_type_ids, $rule->equipment_type_ids());
		$level_names = [];
		foreach($rule->levels() as $level) {
			$level_id = $level->id();
			self::assertIsInt($level_id);
			self::assertGreaterThan(0, $level_id);
			self::assertSame($id, $level->badge_rule_id());
			switch($level->name()) {
				case $level1_name:
					self::assertSame($level1_uses, $level->uses());
					break;
				case $level2_name:
					self::assertSame($level2_uses, $level->uses());
					break;
				case $level3_name:
					self::assertSame($level3_uses, $level->uses());
					break;
			}
			$level_names[] = $level->name();
		}
		self::assertEqualsCanonicalizing(
			$level_names,
			[
				$level1_name,
				$level2_name,
				$level3_name
			]
		);

		self::assertNull($badge_rule_model->read($id));
		self::assertNull($badge_rule_model->update($rule));
		self::assertNull($badge_rule_model->delete($id));

		// cleanup
		$equipment_type_model->delete($equipment_type_id1);
		$equipment_type_model->delete($equipment_type_id2);
		$equipment_type_model->delete($equipment_type_id3);
	}

	public function testSearch() {
		$badge_rule_model = new BadgeRuleModel(Config::config());

		$name1 = 'Welding Novice';
		$name2 = 'Welding Pro';

		$rule1Id = $badge_rule_model->create(
			(new BadgeRule())
				->set_name($name1)
		)->id();

		$rule2Id = $badge_rule_model->create(
			(new BadgeRule())
				->set_name($name2)
		)->id();

		$rules = $badge_rule_model->search();

		self::assertIsIterable($rules);
		self::assertNotEmpty($rules);
		self::assertContainsOnly(BadgeRule::class, $rules);

		$ruleIds = array_map(
			fn (BadgeRule $rule) => $rule->id(),
			$rules
		);

		self::assertContains($rule1Id, $ruleIds);
		self::assertContains($rule2Id, $ruleIds);

		// cleanup
		$badge_rule_model->delete($rule1Id);
		$badge_rule_model->delete($rule2Id);
	}
}
