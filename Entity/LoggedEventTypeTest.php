<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Portalbox\Entity\LoggedEventType;

final class LoggedEventTypeTest extends TestCase {
	public function testValidity(): void {
		self::assertTrue(LoggedEventType::is_valid(LoggedEventType::DEAUTHENTICATION));
		self::assertFalse(LoggedEventType::is_valid(-1));
	}
}