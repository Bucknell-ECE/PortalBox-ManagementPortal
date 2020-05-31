<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Portalbox\Entity\ChargePolicy;
use PortalBox\Entity\EquipmentType;
use Portalbox\Transform\EquipmentTypeTransformer;

final class EquipmentTypeTransformerTest extends TestCase {
	public function testDeserialize(): void {
		$transformer = new EquipmentTypeTransformer();

		$id = 42;
		$name = 'laser scalpel';
		$requires_training = TRUE;
		$charge_rate = "2.50";
		$charge_policy_id = ChargePolicy::PER_USE;
		$charge_policy = ChargePolicy::name_for_policy($charge_policy_id);

		$data = [
			'id' => $id,
			'name' => $name,
			'requires_training' => $requires_training,
			'charge_rate' => $charge_rate,
			'charge_policy_id' => $charge_policy_id
		];

		$type = $transformer->deserialize($data);

		self::assertNotNull($type);
		self::assertNull($type->id());
		self::assertEquals($name, $type->name());
		self::assertEquals($requires_training, $type->requires_training());
		self::assertEquals($charge_rate, $type->charge_rate());
		self::assertEquals($charge_policy_id, $type->charge_policy_id());
		self::assertEquals($charge_policy, $type->charge_policy());
	}

	public function testDeserializeInvalidDataName(): void {
		$transformer = new EquipmentTypeTransformer();

		$id = 42;
		$requires_training = TRUE;
		$charge_rate = "2.50";
		$charge_policy_id = ChargePolicy::PER_USE;

		$data = [
			'id' => $id,
			'requires_training' => $requires_training,
			'charge_rate' => $charge_rate,
			'charge_policy_id' => $charge_policy_id
		];

		$this->expectException(InvalidArgumentException::class);
		$type = $transformer->deserialize($data);
	}

	public function testDeserializeInvalidDataRequireTraining(): void {
		$transformer = new EquipmentTypeTransformer();

		$id = 42;
		$name = 'laser scalpel';
		$charge_rate = "2.50";
		$charge_policy_id = ChargePolicy::PER_USE;

		$data = [
			'id' => $id,
			'name' => $name,
			'charge_rate' => $charge_rate,
			'charge_policy_id' => $charge_policy_id
		];

		$this->expectException(InvalidArgumentException::class);
		$type = $transformer->deserialize($data);
	}

	public function testDeserializeInvalidDataChargeRate(): void {
		$transformer = new EquipmentTypeTransformer();

		$id = 42;
		$name = 'laser scalpel';
		$requires_training = TRUE;
		$charge_policy_id = ChargePolicy::PER_USE;

		$data = [
			'id' => $id,
			'name' => $name,
			'requires_training' => $requires_training,
			'charge_policy_id' => $charge_policy_id
		];

		$this->expectException(InvalidArgumentException::class);
		$type = $transformer->deserialize($data);
	}

	public function testDeserializeInvalidDataChargePolicy(): void {
		$transformer = new EquipmentTypeTransformer();

		$id = 42;
		$name = 'laser scalpel';
		$requires_training = TRUE;
		$charge_rate = "2.50";

		$data = [
			'id' => $id,
			'name' => $name,
			'requires_training' => $requires_training,
			'charge_rate' => $charge_rate
		];

		$this->expectException(InvalidArgumentException::class);
		$type = $transformer->deserialize($data);
	}

	public function testSerialize(): void {
		$transformer = new EquipmentTypeTransformer();

		$id = 42;
		$name = 'laser scalpel';
		$requires_training = TRUE;
		$charge_rate = "2.50";
		$charge_policy_id = ChargePolicy::PER_USE;
		$charge_policy = ChargePolicy::name_for_policy($charge_policy_id);

		$type = (new EquipmentType())
			->set_id($id)
			->set_name($name)
			->set_requires_training($requires_training)
			->set_charge_rate($charge_rate)
			->set_charge_policy_id($charge_policy_id);

		$data = $transformer->serialize($type, true);

		self::assertNotNull($data);
		self::assertArrayHasKey('id', $data);
		self::assertEquals($id, $data['id']);
		self::assertArrayHasKey('name', $data);
		self::assertEquals($name, $data['name']);
		self::assertArrayHasKey('requires_training', $data);
		self::assertEquals($requires_training, $data['requires_training']);
		self::assertArrayHasKey('charge_rate', $data);
		self::assertEquals($charge_rate, $data['charge_rate']);
		self::assertArrayHasKey('charge_policy_id', $data);
		self::assertEquals($charge_policy_id, $data['charge_policy_id']);
		self::assertArrayHasKey('charge_policy', $data);
		self::assertEquals($charge_policy, $data['charge_policy']);
	}
}