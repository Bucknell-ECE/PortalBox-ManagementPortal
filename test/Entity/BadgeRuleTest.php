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

		$rule = (new BadgeRule())
			->set_id($id)
			->set_name($name);

		self::assertEquals($id, $rule->id());
		self::assertEquals($name, $rule->name());
	}

	public function testExceptionThrownOnInvalidName(): void {
		self::expectException(InvalidArgumentException::class);
		(new BadgeRule())->set_name('');
	}
}
