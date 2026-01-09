<?php

declare(strict_types=1);

namespace Test\Portalbox\Transform;

use PHPUnit\Framework\TestCase;
use PortalBox\Entity\BadgeRule;
use Portalbox\Transform\BadgeRuleTransformer;

final class BadgeRuleTransformerTest extends TestCase {
	public function testSerialize(): void {
		$transformer = new BadgeRuleTransformer();

		$id = 42;
		$name = 'Master Gardener';

		$key = (new BadgeRule())
			->set_id($id)
			->set_name($name);

		$data = $transformer->serialize($key, true);

		self::assertIsArray($data);
		self::assertArrayHasKey('id', $data);
		self::assertEquals($id, $data['id']);
		self::assertArrayHasKey('name', $data);
		self::assertEquals($name, $data['name']);
	}
}
