<?php

declare(strict_types=1);

namespace Test\Portalbox\Type;

use PHPUnit\Framework\TestCase;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Model\Type\TrainingCard;
use Portalbox\Type\EquipmentType;

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
