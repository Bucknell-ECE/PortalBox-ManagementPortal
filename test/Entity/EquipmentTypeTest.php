<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Portalbox\Entity\ChargePolicy;
use Portalbox\Entity\EquipmentType;

final class EquipmentTypeTest extends TestCase {
	public function testAgreement(): void {
		$id = 42;
		$name = 'laser scalpel';
		$requires_training = TRUE;
		$charge_rate = "2.50";
		$charge_policy_id = ChargePolicy::PER_USE;

		$type = (new EquipmentType())
			->set_id($id)
			->set_name($name)
			->set_requires_training($requires_training)
			->set_charge_rate($charge_rate)
			->set_charge_policy_id($charge_policy_id);

		self::assertEquals($id, $type->id());
		self::assertEquals($name, $type->name());
		self::assertEquals($requires_training, $type->requires_training());
		self::assertEquals($charge_rate, $type->charge_rate());
		self::assertEquals($charge_policy_id, $type->charge_policy_id());
	}

	public function testInvalidChargePolicyTriggersException(): void {
		$this->expectException(InvalidArgumentException::class);

		$card = (new EquipmentType())->set_charge_policy_id(-1);
	}
}