<?php

declare(strict_types=1);

namespace Test\Portalbox\Service;

use PHPUnit\Framework\TestCase;
use Portalbox\Entity\CardType;
use Portalbox\Entity\Permission;
use Portalbox\Entity\Role;
use Portalbox\Entity\User;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Model\CardTypeModel;
use Portalbox\Service\CardTypeService;
use Portalbox\Session;

final class CardTypeServiceTest extends TestCase {
	public function testReadAllThrowsWhenNotAuthenticated() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$cardTypeModel = $this->createStub(CardTypeModel::class);

		$service = new CardTypeService(
			$session,
			$cardTypeModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(CardTypeService::ERROR_UNAUTHENTICATED_READ);
		$service->readAll();
	}

	public function testReadAllThrowsWhenNotAuthorized() {
		$cardTypes = [
			new CardType()
		];

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
				)
		);

		$cardTypeModel = $this->createStub(CardTypeModel::class);

		$service = new CardTypeService(
			$session,
			$cardTypeModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(CardTypeService::ERROR_UNAUTHORIZED_READ);
		self::assertSame($cardTypes, $service->readAll());
	}

	public function testReadAllSuccess() {
		$cardTypes = [
			new CardType()
		];

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::LIST_CARD_TYPES])
				)
		);

		$cardTypeModel = $this->createMock(CardTypeModel::class);
		$cardTypeModel->expects($this->once())->method('search')->willReturn($cardTypes);

		$service = new CardTypeService(
			$session,
			$cardTypeModel
		);

		self::assertSame($cardTypes, $service->readAll());
	}
}
