<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Portalbox\Config;
use Portalbox\Entity\ChargePolicy;
use Portalbox\Entity\EquipmentType;
use Portalbox\Model\EquipmentTypeModel;

final class EquipmentTypeModelTest extends TestCase {
	public function testModel(): void {
		$model = new EquipmentTypeModel(Config::config());

		$name = 'ceramics printer';
		$requires_training = TRUE;
		$charge_rate = "0.01";
		$charge_policy_id = ChargePolicy::PER_MINUTE;

		$type = (new EquipmentType())
			->set_name($name)
			->set_requires_training($requires_training)
			->set_charge_rate($charge_rate)
			->set_charge_policy_id($charge_policy_id);

		$type_as_created = $model->create($type);

		$type_id = $type_as_created->id();
		self::assertIsInt($type_id);
		self::assertEquals($name, $type_as_created->name());
		self::assertEquals($requires_training, $type_as_created->requires_training());
		self::assertEquals($charge_rate, $type_as_created->charge_rate());
		self::assertEquals($charge_policy_id, $type_as_created->charge_policy_id());

		$type_as_found = $model->read($type_id);

		self::assertNotNull($type_as_found);
		self::assertEquals($type_id, $type_as_found->id());
		self::assertEquals($name, $type_as_found->name());
		self::assertEquals($requires_training, $type_as_found->requires_training());
		self::assertEquals($charge_rate, $type_as_found->charge_rate());
		self::assertEquals($charge_policy_id, $type_as_found->charge_policy_id());

		$name = '3D Clay Printer';
		$requires_training = FALSE;
		$charge_rate = '2.50';
		$charge_policy_id = ChargePolicy::PER_USE;

		$type_as_found
			->set_name($name)
			->set_requires_training($requires_training)
			->set_charge_rate($charge_rate)
			->set_charge_policy_id($charge_policy_id);

		$type_as_modified = $model->update($type_as_found);

		self::assertNotNull($type_as_modified);
		self::assertEquals($type_id, $type_as_modified->id());
		self::assertEquals($name, $type_as_modified->name());
		self::assertEquals($requires_training, $type_as_modified->requires_training());
		self::assertEquals($charge_rate, $type_as_modified->charge_rate());
		self::assertEquals($charge_policy_id, $type_as_modified->charge_policy_id());

		$types_as_found = $model->search();
		self::assertIsArray($types_as_found);
		self::assertNotEmpty($types_as_found);
		self::assertContainsOnlyInstancesOf(EquipmentType::class, $types_as_found);

		$type_as_deleted = $model->delete($type_id);

		self::assertNotNull($type_as_deleted);
		self::assertEquals($type_id, $type_as_deleted->id());
		self::assertEquals($name, $type_as_deleted->name());
		self::assertEquals($requires_training, $type_as_deleted->requires_training());
		self::assertEquals($charge_rate, $type_as_deleted->charge_rate());
		self::assertEquals($charge_policy_id, $type_as_deleted->charge_policy_id());

		$type_as_not_found = $model->read($type_id);

		self::assertNull($type_as_not_found);
	}
}