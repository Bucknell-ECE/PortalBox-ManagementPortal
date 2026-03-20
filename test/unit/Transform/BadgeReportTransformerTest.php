<?php

declare(strict_types=1);

namespace Test\Portalbox\Transform;

use PHPUnit\Framework\TestCase;
use Portalbox\Transform\BadgeReportTransformer;
use Portalbox\Type\BadgeLevel;

final class BadgeReportTransformerTest extends TestCase {
	public function testSerialize(): void {
		$name = 'Makerspace User';
		$email = 'user@maker.space';
		$badgeName1 = 'Welder';
		$badgeName2 = 'Mechanic';

		$badges = [
			(new BadgeLevel())->set_name($badgeName1),
			(new BadgeLevel())->set_name($badgeName2)
		];

		$reportRecord = [
			$name,
			$email,
			$badges
		];

		$data = (new BadgeReportTransformer())->serialize($reportRecord);

		self::assertIsArray($data);
		self::assertArrayHasKey('name', $data);
		self::assertEquals($name, $data['name']);
		self::assertArrayHasKey('email', $data);
		self::assertEquals($email, $data['email']);
		self::assertArrayHasKey('badges', $data);
		self::assertIsArray($data['badges']);
		self::assertCount(2, $data['badges']);
		self::assertContains($badgeName1, $data['badges']);
		self::assertContains($badgeName2, $data['badges']);
	}
}
