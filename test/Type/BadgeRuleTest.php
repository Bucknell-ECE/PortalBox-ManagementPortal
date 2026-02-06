<?php

declare(strict_types=1);

namespace Test\Portalbox\Type;

use PHPUnit\Framework\TestCase;
use Portalbox\Type\BadgeLevel;
use Portalbox\Type\BadgeRule;

final class BadgeRuleTest extends TestCase {
	public function testAgreement(): void {
		$badge_rule_id = 12;
		$name = 'Welder';
		$equipment_type_ids = [2,4];

		$levels = [
			(new BadgeLevel())
				->set_id(42)
				->set_name('Journeyman Welder')
				->set_badge_rule_id($badge_rule_id)
				->set_uses(10),
			(new BadgeLevel())
				->set_id(43)
				->set_name('Pro Welder')
				->set_badge_rule_id($badge_rule_id)
				->set_uses(100)
		];

		$rule = (new BadgeRule())
			->set_id($badge_rule_id)
			->set_name($name)
			->set_equipment_type_ids($equipment_type_ids)
			->set_levels($levels);

		self::assertSame($badge_rule_id, $rule->id());
		self::assertSame($name, $rule->name());
		self::assertSame($equipment_type_ids, $rule->equipment_type_ids());
		self::assertSame($levels, $rule->levels());
	}
}
