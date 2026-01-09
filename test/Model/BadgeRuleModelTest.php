<?php

declare(strict_types=1);

namespace Test\Portalbox\Model;

use PHPUnit\Framework\TestCase;
use Portalbox\Config;
use Portalbox\Entity\BadgeRule;
use Portalbox\Model\BadgeRuleModel;

final class BadgeRuleModelTest extends TestCase {
	public function testCreateReadUpdateDelete(): void {
		$model = new BadgeRuleModel(Config::config());

		$name = 'Welding Novice';

		$rule = $model->create(
			(new BadgeRule())
				->set_name($name)
		);

		self::assertInstanceOf(BadgeRule::class, $rule);
		$id = $rule->id();
		self::assertIsInt($id);
		self::assertEquals($name, $rule->name());

		$rule = $model->read($id);

		self::assertInstanceOf(BadgeRule::class, $rule);
		self::assertEquals($id, $rule->id());
		self::assertEquals($name, $rule->name());

		$name = 'Welding Pro';

		$rule = $model->update(
			(new BadgeRule())
				->set_id($id)
				->set_name($name)
		);

		self::assertInstanceOf(BadgeRule::class, $rule);
		self::assertEquals($id, $rule->id());
		self::assertEquals($name, $rule->name());

		$rule = $model->delete($id);

		self::assertInstanceOf(BadgeRule::class, $rule);
		self::assertEquals($id, $rule->id());
		self::assertEquals($name, $rule->name());

		self::assertNull($model->read($id));
		self::assertNull($model->update($rule));
		self::assertNull($model->delete($id));
	}

	public function testSearch() {
		$model = new BadgeRuleModel(Config::config());

		$name1 = 'Welding Novice';
		$name2 = 'Welding Pro';

		$rule1Id = $model->create(
			(new BadgeRule())
				->set_name($name1)
		)->id();

		$rule2Id = $model->create(
			(new BadgeRule())
				->set_name($name2)
		)->id();

		$rules = $model->search();

		self::assertIsIterable($rules);
		self::assertNotEmpty($rules);
		self::assertContainsOnly(BadgeRule::class, $rules);

		$ruleIds = array_map(
			fn (BadgeRule $rule) => $rule->id(),
			$rules
		);

		self::assertContains($rule1Id, $ruleIds);
		self::assertContains($rule2Id, $ruleIds);

		// cleanup
		$model->delete($rule1Id);
		$model->delete($rule2Id);
	}
}
