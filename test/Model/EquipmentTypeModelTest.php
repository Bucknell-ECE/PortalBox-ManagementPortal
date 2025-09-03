<?php

declare(strict_types=1);

namespace Test\Portalbox\Model;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Portalbox\Config;
use Portalbox\Entity\ChargePolicy;
use Portalbox\Entity\EquipmentType;
use Portalbox\Model\EquipmentTypeModel;

final class EquipmentTypeModelTest extends TestCase {
	public function testCreateReadUpdateDelete(): void {
		$model = new EquipmentTypeModel(Config::config());

		$name = 'ceramics printer';
		$requires_training = true;
		$charge_rate = "0.01";
		$charge_policy_id = ChargePolicy::PER_MINUTE;
		$allow_proxy = true;

		$type = (new EquipmentType())
			->set_name($name)
			->set_requires_training($requires_training)
			->set_charge_rate($charge_rate)
			->set_charge_policy_id($charge_policy_id)
			->set_allow_proxy($allow_proxy);

		$type_as_created = $model->create($type);

		$type_id = $type_as_created->id();
		self::assertIsInt($type_id);
		self::assertEquals($name, $type_as_created->name());
		self::assertEquals($requires_training, $type_as_created->requires_training());
		self::assertEquals($charge_rate, $type_as_created->charge_rate());
		self::assertEquals($charge_policy_id, $type_as_created->charge_policy_id());
		self::assertEquals($allow_proxy, $type_as_created->allow_proxy());

		$type_as_found = $model->read($type_id);

		self::assertNotNull($type_as_found);
		self::assertEquals($type_id, $type_as_found->id());
		self::assertEquals($name, $type_as_found->name());
		self::assertEquals($requires_training, $type_as_found->requires_training());
		self::assertEquals($charge_rate, $type_as_found->charge_rate());
		self::assertEquals($charge_policy_id, $type_as_found->charge_policy_id());
		self::assertEquals($allow_proxy, $type_as_found->allow_proxy());

		$name = '3D Clay Printer';
		$requires_training = false;
		$charge_rate = '2.50';
		$charge_policy_id = ChargePolicy::PER_USE;
		$allow_proxy = false;

		$type_as_found
			->set_name($name)
			->set_requires_training($requires_training)
			->set_charge_rate($charge_rate)
			->set_charge_policy_id($charge_policy_id)
			->set_allow_proxy($allow_proxy);

		$type_as_modified = $model->update($type_as_found);

		self::assertNotNull($type_as_modified);
		self::assertEquals($type_id, $type_as_modified->id());
		self::assertEquals($name, $type_as_modified->name());
		self::assertEquals($requires_training, $type_as_modified->requires_training());
		self::assertEquals($charge_rate, $type_as_modified->charge_rate());
		self::assertEquals($charge_policy_id, $type_as_modified->charge_policy_id());
		self::assertEquals($allow_proxy, $type_as_modified->allow_proxy());

		$type_as_deleted = $model->delete($type_id);

		self::assertNotNull($type_as_deleted);
		self::assertEquals($type_id, $type_as_deleted->id());
		self::assertEquals($name, $type_as_deleted->name());
		self::assertEquals($requires_training, $type_as_deleted->requires_training());
		self::assertEquals($charge_rate, $type_as_deleted->charge_rate());
		self::assertEquals($charge_policy_id, $type_as_deleted->charge_policy_id());
		self::assertEquals($allow_proxy, $type_as_deleted->allow_proxy());

		self::assertNull($model->read($type_id));
	}

	public function testSearchThrowsWhenSortColumnInvalid() {
		$model = new EquipmentTypeModel(Config::config());

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(EquipmentTypeModel::ERROR_INVALID_SORT_COLUMN);
		$model->search('popularity');
	}

	public function testSearch() {
		$model = new EquipmentTypeModel(Config::config());

		$name1 = 'ceramics printer';
		$name2 = '3D Printer';
		$requires_training = true;
		$charge_rate = "0.01";
		$charge_policy_id = ChargePolicy::PER_MINUTE;
		$allow_proxy = true;

		$typeId1 = $model->create(
			(new EquipmentType())
				->set_name($name1)
				->set_requires_training($requires_training)
				->set_charge_rate($charge_rate)
				->set_charge_policy_id($charge_policy_id)
				->set_allow_proxy($allow_proxy)
		)->id();

		$typeId2 = $model->create(
			(new EquipmentType())
				->set_name($name2)
				->set_requires_training($requires_training)
				->set_charge_rate($charge_rate)
				->set_charge_policy_id($charge_policy_id)
				->set_allow_proxy($allow_proxy)
		)->id();

		// check that we can search without sorting
		$typeIds = array_map(
			fn(EquipmentType $type) => $type->id(),
			$model->search()
		);
		self::assertContains($typeId1, $typeIds);
		self::assertContains($typeId2, $typeIds);

		// check that we can sort by name
		$typeIds = array_map(
			fn(EquipmentType $type) => $type->id(),
			$model->search('name')
		);
		self::assertContains($typeId1, $typeIds);
		self::assertContains($typeId2, $typeIds);

		$index1 = array_search($typeId1, $typeIds);
		$index2 = array_search($typeId2, $typeIds);
		self::assertLessThan($index1, $index2);

		// check that we can sort by id
		$typeIds = array_map(
			fn(EquipmentType $type) => $type->id(),
			$model->search('id')
		);
		self::assertContains($typeId1, $typeIds);
		self::assertContains($typeId2, $typeIds);

		$index1 = array_search($typeId1, $typeIds);
		$index2 = array_search($typeId2, $typeIds);
		self::assertLessThan($index2, $index1);

		// cleanup
		$model->delete($typeId1);
		$model->delete($typeId2);
	}
}
