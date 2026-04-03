<?php

declare(strict_types=1);

namespace Test\Portalbox\Service;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Portalbox\Enumeration\Permission;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Exception\NotFoundException;
use Portalbox\Model\ChargeModel;
use Portalbox\Model\EquipmentModel;
use Portalbox\Query\ChargeQuery;
use Portalbox\Service\ChargeService;
use Portalbox\Session;
use Portalbox\Type\Charge;
use Portalbox\Type\Equipment;
use Portalbox\Type\Role;
use Portalbox\Type\User;

final class ChargeServiceTest extends TestCase {
	#region test read()

	public function testReadThrowsWhenNotAuthenticated() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$chargeModel = $this->createStub(ChargeModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);

		$service = new ChargeService(
			$session,
			$chargeModel,
			$equipmentModel
 		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(ChargeService::ERROR_UNAUTHENTICATED_READ);
		$service->read(19);
	}

	public function testReadThrowsWhenNotAuthorized() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$chargeModel = $this->createStub(ChargeModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);

		$service = new ChargeService(
			$session,
			$chargeModel,
			$equipmentModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(ChargeService::ERROR_UNAUTHORIZED_READ);
		$service->read(19);
	}

	public function testReadThrowsWhenChargeDoesNotExist() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::READ_CHARGE])
				)
		);

		$chargeModel = $this->createStub(ChargeModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);

		$service = new ChargeService(
			$session,
			$chargeModel,
			$equipmentModel
		);

		self::expectException(NotFoundException::class);
		self::expectExceptionMessage(ChargeService::ERROR_CHARGE_NOT_FOUND);
		$service->read(19);
	}

	public function testReadSuccess() {
		$charge = new Charge();

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::READ_CHARGE])
				)
		);

		$chargeModel = $this->createStub(ChargeModel::class);
		$chargeModel->method('read')->willReturn($charge);

		$equipmentModel = $this->createStub(EquipmentModel::class);

		$service = new ChargeService(
			$session,
			$chargeModel,
			$equipmentModel
		);

		self::assertSame($charge, $service->read(19));
	}

	#endregion test read()

	#region test readAll()

	public function testReadAllThrowsWhenNotAuthenticated() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$chargeModel = $this->createStub(ChargeModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);

		$service = new ChargeService(
			$session,
			$chargeModel,
			$equipmentModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(ChargeService::ERROR_UNAUTHENTICATED_READ);
		$service->readAll([]);
	}

	public function testReadAllThrowsWhenNotAuthorized() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$chargeModel = $this->createStub(ChargeModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);

		$service = new ChargeService(
			$session,
			$chargeModel,
			$equipmentModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(ChargeService::ERROR_UNAUTHORIZED_READ);
		$service->readAll([]);
	}

	public function testReadAllThrowsWhenUserMayReadOwnChargeButUserFilterNotSet() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_id(1)
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::LIST_OWN_CHARGES])
				)
		);

		$chargeModel = $this->createStub(ChargeModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);

		$service = new ChargeService(
			$session,
			$chargeModel,
			$equipmentModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(ChargeService::ERROR_UNAUTHORIZED_READ);
		$service->readAll([]);
	}

	public function testReadAllThrowsWhenUserMayReadOwnChargeButUserFilterNotUserId() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_id(1)
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::LIST_OWN_CHARGES])
				)
		);

		$chargeModel = $this->createStub(ChargeModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);

		$service = new ChargeService(
			$session,
			$chargeModel,
			$equipmentModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(ChargeService::ERROR_UNAUTHORIZED_READ);
		$service->readAll(['user_id' => '2']);
	}

	public function testReadAllThrowsWhenUserFilterIsNotInt() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::LIST_CHARGES])
				)
		);

		$chargeModel = $this->createStub(ChargeModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);

		$service = new ChargeService(
			$session,
			$chargeModel,
			$equipmentModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(ChargeService::ERROR_USER_FILTER_MUST_BE_INT);
		$service->readAll(['user_id' => 'apple']);
	}

	public function testReadAllThrowsWhenAfterFilterIsNotDate() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::LIST_CHARGES])
				)
		);

		$chargeModel = $this->createStub(ChargeModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);

		$service = new ChargeService(
			$session,
			$chargeModel,
			$equipmentModel
		);

		self::expectException(Exception::class);
		// we rely on PHP's exception message which can change without notice so no assertion
		$service->readAll(['after' => 'apple']);
	}

	public function testReadAllThrowsWhenBeforeFilterIsNotDate() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::LIST_CHARGES])
				)
		);

		$chargeModel = $this->createStub(ChargeModel::class);
		$equipmentModel = $this->createStub(EquipmentModel::class);

		$service = new ChargeService(
			$session,
			$chargeModel,
			$equipmentModel
		);

		self::expectException(Exception::class);
		// we rely on PHP's exception message which can change without notice so no assertion
		$service->readAll(['before' => 'apple']);
	}

	public function testReadAllSuccessAsAdmin() {
		$user_id = 2;
		$after = '2025-03-01';
		$before = '2026-03-19';
		$charge1_id = 300;
		$charge2_id = 305;
		$equipment1_id = 3;
		$equipment2_id = 5;

		$charge1 = (new Charge())
			->set_id($charge1_id)
			->set_equipment_id($equipment1_id);
		$charge2 = (new Charge())
			->set_id($charge2_id)
			->set_equipment_id($equipment2_id);

		$equipment1 = (new Equipment())->set_id($equipment1_id);
		$equipment2 = (new Equipment())->set_id($equipment2_id);

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_id(1)
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::LIST_CHARGES])
				)
		);

		$chargeModel = $this->createMock(ChargeModel::class);
		$chargeModel->expects($this->once())->method('search')->with(
			$this->callback(
				fn(ChargeQuery $query) =>
					$query->user_id() === $user_id
					&& $query->on_or_after()->format('Y-m-d') === $after
					&& $query->on_or_before()->format('Y-m-d') === $before
			)
		)->willReturn([
			$charge1,
			$charge2
		]);

		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentModel->method('search')->willReturn([
			$equipment1,
			$equipment2
		]);

		$service = new ChargeService(
			$session,
			$chargeModel,
			$equipmentModel
		);

		$charges = $service->readAll([
			'user_id' => $user_id,
			'after' => $after,
			'before' => $before
		]);

		$charge_ids = [];
		foreach ($charges as $charge) {
			$id = $charge->id();
			switch ($id) {
				case $charge1_id:
					self::assertSame($charge->equipment(), $equipment1);
					break;
				case $charge2_id:
					self::assertSame($charge->equipment(), $equipment2);
					break;
			}
			$charge_ids[] = $id;
		}
		self::assertEqualsCanonicalizing($charge_ids, [$charge1_id, $charge2_id]);
	}

	public function testReadAllSuccessForOwnCharges() {
		$user_id = 2;
		$after = '2025-03-01';
		$before = '2026-03-19';
		$charge1_id = 300;
		$charge2_id = 305;
		$equipment1_id = 3;
		$equipment2_id = 5;

		$charge1 = (new Charge())
			->set_id($charge1_id)
			->set_equipment_id($equipment1_id);
		$charge2 = (new Charge())
			->set_id($charge2_id)
			->set_equipment_id($equipment2_id);

		$equipment1 = (new Equipment())->set_id($equipment1_id);
		$equipment2 = (new Equipment())->set_id($equipment2_id);

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_id($user_id)
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::LIST_OWN_CHARGES])
				)
		);

		$chargeModel = $this->createMock(ChargeModel::class);
		$chargeModel->expects($this->once())->method('search')->with(
			$this->callback(
				fn(ChargeQuery $query) =>
					$query->user_id() === $user_id
					&& $query->on_or_after()->format('Y-m-d') === $after
					&& $query->on_or_before()->format('Y-m-d') === $before
			)
		)->willReturn([
			$charge1,
			$charge2
		]);

		$equipmentModel = $this->createStub(EquipmentModel::class);
		$equipmentModel->method('search')->willReturn([
			$equipment1,
			$equipment2
		]);

		$service = new ChargeService(
			$session,
			$chargeModel,
			$equipmentModel
		);

		$charges = $service->readAll([
			'user_id' => $user_id,
			'after' => $after,
			'before' => $before
		]);

		$charge_ids = [];
		foreach ($charges as $charge) {
			$id = $charge->id();
			switch ($id) {
				case $charge1_id:
					self::assertSame($charge->equipment(), $equipment1);
					break;
				case $charge2_id:
					self::assertSame($charge->equipment(), $equipment2);
					break;
			}
			$charge_ids[] = $id;
		}
		self::assertEqualsCanonicalizing($charge_ids, [$charge1_id, $charge2_id]);
	}

	#endregion test readAll()
}
