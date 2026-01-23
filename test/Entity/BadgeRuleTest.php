<?php

declare(strict_types=1);

namespace Test\Portalbox\Entity;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Portalbox\Entity\BadgeRule;

final class BadgeRuleTest extends TestCase {
	public function testAgreement(): void {
		$id = 42;
		$name = 'Welding Pro';
		$equipment_type_ids = [2,4];

		$rule = (new BadgeRule())
			->set_id($id)
			->set_name($name)
			->set_equipment_type_ids($equipment_type_ids);

		self::assertSame($id, $rule->id());
		self::assertSame($name, $rule->name());
		self::assertSame($equipment_type_ids, $rule->equipment_type_ids());
	}

	public function testExceptionThrownOnInvalidName(): void {
		self::expectException(InvalidArgumentException::class);
		(new BadgeRule())->set_name('');
	}
}
