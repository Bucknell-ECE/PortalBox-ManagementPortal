<?php

declare(strict_types=1);

namespace Test\Portalbox\Transform;

use PHPUnit\Framework\TestCase;
use Portalbox\Transform\BadgeRuleTransformer;
use Portalbox\Type\BadgeLevel;
use Portalbox\Type\BadgeRule;

final class BadgeRuleTransformerTest extends TestCase {
	public function testSerialize(): void {
		$transformer = new BadgeRuleTransformer();

		$id = 12;
		$name = 'Gardener';
		$equipment_type_ids = [21, 34];

		$level1_name = 'Home Gardener';
		$level1_image = 'greenThumb.svg';
		$level1_uses = 10;
		$level2_name = 'Landscaper';
		$level2_image = 'wheelbarrow.svg';
		$level2_uses = 100;
		$levels = [
			(new BadgeLevel())
				->set_id(42)
				->set_badge_rule_id($id)
				->set_name($level1_name)
				->set_image($level1_image)
				->set_uses($level1_uses),
			(new BadgeLevel())
				->set_id(43)
				->set_badge_rule_id($id)
				->set_name($level2_name)
				->set_image($level2_image)
				->set_uses($level2_uses)
		];

		$rule = (new BadgeRule())
			->set_id($id)
			->set_name($name)
			->set_equipment_type_ids($equipment_type_ids)
			->set_levels($levels);

		$data = $transformer->serialize($rule, true);

		self::assertIsArray($data);
		self::assertArrayHasKey('id', $data);
		self::assertEquals($id, $data['id']);
		self::assertArrayHasKey('name', $data);
		self::assertEquals($name, $data['name']);
		self::assertArrayHasKey('equipment_types', $data);
		self::assertEquals($equipment_type_ids, $data['equipment_types']);
		self::assertArrayHasKey('levels', $data);
		$level_names = [];
		foreach($data['levels'] as $level) {
			self::assertArrayHasKey('id', $level);
			$level_id = $level['id'];
			self::assertIsInt($level_id);
			self::assertGreaterThan(0, $level_id);
			self::assertArrayHasKey('badge_rule_id', $level);
			self::assertSame($id, $level['badge_rule_id']);
			self::assertArrayHasKey('name', $level);
			self::assertArrayHasKey('image', $level);
			self::assertArrayHasKey('uses', $level);
			switch($level['name']) {
				case $level1_name:
					self::assertSame($level1_image, $level['image']);
					self::assertSame($level1_uses, $level['uses']);
					break;
				case $level2_name:
					self::assertSame($level2_image, $level['image']);
					self::assertSame($level2_uses, $level['uses']);
					break;
			}
			$level_names[] = $level['name'];
		}
		self::assertEqualsCanonicalizing(
			$level_names,
			[
				$level1_name,
				$level2_name
			]
		);
	}
}
