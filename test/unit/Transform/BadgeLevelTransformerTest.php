<?php

declare(strict_types=1);

namespace Test\Portalbox\Transform;

use PHPUnit\Framework\TestCase;
use Portalbox\Transform\BadgeLevelTransformer;
use Portalbox\Type\BadgeLevel;

final class BadgeLevelTransformerTest extends TestCase {
	public function testSerialize(): void {
		$transformer = new BadgeLevelTransformer();

		$name = 'Home Gardener';
		$image = 'greenThumb.svg';
		$uses = 10;

		$level = (new BadgeLevel())
			->set_id(42)
			->set_badge_rule_id(2)
			->set_name($name)
			->set_image($image)
			->set_uses($uses);

		$data = $transformer->serialize($level, true);

		self::assertIsArray($data);
		self::assertArrayHasKey('name', $data);
		self::assertSame($name, $data['name']);
		self::assertArrayHasKey('image', $data);
		self::assertSame($image, $data['image']);
		self::assertArrayHasKey('uses', $data);
		self::assertSame($uses, $data['uses']);
	}
}
