<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Portalbox\Entity\ChargePolicy;

final class ChargePolicyTest extends TestCase {
	public function testValidity(): void {
		self::assertTrue(ChargePolicy::is_valid(ChargePolicy::PER_USE));
		self::assertFalse(ChargePolicy::is_valid(-1));
	}
}