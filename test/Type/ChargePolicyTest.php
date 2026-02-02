<?php

declare(strict_types=1);

namespace Test\Portalbox\Type;

use PHPUnit\Framework\TestCase;
use Portalbox\Type\ChargePolicy;

final class ChargePolicyTest extends TestCase {
	public function testValidity(): void {
		self::assertTrue(ChargePolicy::is_valid(ChargePolicy::PER_USE));
		self::assertFalse(ChargePolicy::is_valid(-1));
	}
}
