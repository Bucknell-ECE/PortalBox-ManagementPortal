<?php

declare(strict_types=1);

namespace Test\Portalbox\Type;

use PHPUnit\Framework\TestCase;
use Portalbox\Type\BadgeLevel;

final class BadgeLevelTest extends TestCase {
	public function testAgreement(): void {
		$id = 42;
		$name = 'Welding Pro';
		$image = 'welder.svg';
		$badge_rule_id = 2;
		$uses = 1000;

		$level = (new BadgeLevel())
			->set_id($id)
			->set_badge_rule_id($badge_rule_id)
			->set_name($name)
			->set_image($image)
			->set_uses($uses);

		self::assertSame($id, $level->id());
		self::assertSame($badge_rule_id, $level->badge_rule_id());
		self::assertSame($name, $level->name());
		self::assertSame($image, $level->image());
		self::assertSame($uses, $level->uses());
	}
}