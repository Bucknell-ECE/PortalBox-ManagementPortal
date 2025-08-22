<?php

declare(strict_types=1);

namespace Test\Portalbox\Entity;

use PHPUnit\Framework\TestCase;
use Portalbox\Entity\EquipmentType;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Model\Entity\TrainingCard;

final class TrainingCardTest extends TestCase {
	public function testAgreement(): void {
		$equipmentType = new EquipmentType();

		$model = $this->createStub(EquipmentTypeModel::class);
		$model->method('read')->willReturn($equipmentType);

		$card = (new TrainingCard($model))
			->set_equipment_type_id(1);

		self::assertSame($equipmentType, $card->equipment_type());
	}
}
