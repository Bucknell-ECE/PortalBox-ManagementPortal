<?php

declare(strict_types=1);

namespace Test\Portalbox\Model;

use PHPUnit\Framework\TestCase;
use Portalbox\Config;
use Portalbox\Model\BadgeRuleModel;
use Portalbox\Model\EquipmentTypeModel;
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

		$name = 'Welding Novice';
		$equipment_type_ids = [
			$equipment_type_id1,
			$equipment_type_id2
		];

		$rule = $badge_rule_model->create(
			(new BadgeRule())
				->set_name($name)
				->set_equipment_type_ids($equipment_type_ids)
		);

		self::assertInstanceOf(BadgeRule::class, $rule);
		$id = $rule->id();
		self::assertIsInt($id);
		self::assertSame($name, $rule->name());
		self::assertSame($equipment_type_ids, $rule->equipment_type_ids());

		$rule = $badge_rule_model->read($id);

		self::assertInstanceOf(BadgeRule::class, $rule);
		self::assertSame($id, $rule->id());
		self::assertSame($name, $rule->name());
		self::assertSame($equipment_type_ids, $rule->equipment_type_ids());

		$name = 'Welding Pro';
		$equipment_type_ids = [
			$equipment_type_id1,
			$equipment_type_id3
		];

		$rule = $badge_rule_model->update(
			(new BadgeRule())
				->set_id($id)
				->set_name($name)
				->set_equipment_type_ids($equipment_type_ids)
		);

		self::assertInstanceOf(BadgeRule::class, $rule);
		self::assertSame($id, $rule->id());
		self::assertSame($name, $rule->name());
		self::assertSame($equipment_type_ids, $rule->equipment_type_ids());

		$rule = $badge_rule_model->read($id);

		self::assertInstanceOf(BadgeRule::class, $rule);
		self::assertSame($id, $rule->id());
		self::assertSame($name, $rule->name());
		self::assertSame($equipment_type_ids, $rule->equipment_type_ids());

		$rule = $badge_rule_model->delete($id);

		self::assertInstanceOf(BadgeRule::class, $rule);
		self::assertSame($id, $rule->id());
		self::assertSame($name, $rule->name());
		self::assertSame($equipment_type_ids, $rule->equipment_type_ids());

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
