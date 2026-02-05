<?php

declare(strict_types=1);

namespace Test\Portalbox\Transform;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Portalbox\Enumeration\ChargePolicy;
use Portalbox\Transform\EquipmentTypeTransformer;
use PortalBox\Type\EquipmentType;

final class EquipmentTypeTransformerTest extends TestCase {
	public function testSerialize(): void {
		$transformer = new EquipmentTypeTransformer();

		$id = 42;
		$name = 'laser scalpel';
		$requires_training = true;
		$charge_rate = "2.50";
		$charge_policy = ChargePolicy::PER_USE;
		$allow_proxy = true;

		$type = (new EquipmentType())
			->set_id($id)
			->set_name($name)
			->set_requires_training($requires_training)
			->set_charge_rate($charge_rate)
			->set_charge_policy($charge_policy)
			->set_allow_proxy($allow_proxy);

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
		self::assertArrayHasKey('charge_policy', $data);
		self::assertEquals($charge_policy->value, $data['charge_policy']);
		self::assertArrayHasKey('allow_proxy', $data);
		self::assertEquals($allow_proxy, $data['allow_proxy']);
	}
}
