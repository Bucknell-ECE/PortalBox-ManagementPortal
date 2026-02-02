<?php

declare(strict_types=1);

namespace Test\Portalbox\Transform;

use PHPUnit\Framework\TestCase;
use Portalbox\Transform\BadgeRuleTransformer;
use PortalBox\Type\BadgeRule;

final class BadgeRuleTransformerTest extends TestCase {
	public function testSerialize(): void {
		$transformer = new BadgeRuleTransformer();

		$id = 42;
		$name = 'Master Gardener';
		$equipment_type_ids = [21, 34];

		$key = (new BadgeRule())
			->set_id($id)
			->set_name($name)
			->set_equipment_type_ids($equipment_type_ids);

		$data = $transformer->serialize($key, true);

		self::assertIsArray($data);
		self::assertArrayHasKey('id', $data);
		self::assertEquals($id, $data['id']);
		self::assertArrayHasKey('name', $data);
		self::assertEquals($name, $data['name']);
		self::assertArrayHasKey('equipment_types', $data);
		self::assertEquals($equipment_type_ids, $data['equipment_types']);
	}
}
