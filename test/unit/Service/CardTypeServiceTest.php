<?php

declare(strict_types=1);

namespace Test\Portalbox\Service;

use PHPUnit\Framework\TestCase;
use Portalbox\Enumeration\CardType;
use Portalbox\Enumeration\Permission;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Model\CardTypeModel;
use Portalbox\Service\CardTypeService;
use Portalbox\Session;
use Portalbox\Type\Role;
use Portalbox\Type\User;

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
		$service->readAll();
	}

	public function testReadAllSuccess() {
		$cardTypes = [
			CardType::USER
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
