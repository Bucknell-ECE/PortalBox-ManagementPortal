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
use Portalbox\Session\SessionInterface;

final class CardTypeServiceTest extends TestCase {
	public function testReadAllThrowsWhenNotAuthenticated() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$cardTypeModel = $this->createStub(CardTypeModel::class);

		$service = new CardTypeService(
			$session,
			$cardTypeModel
		);

		self::expectException(AuthenticationException::class);
		$service->readAll();
	}

	public function testReadAllThrowsWhenNotAuthorized() {
		$cardTypes = [
			new CardType()
		];

		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::LIST_CARD_TYPES])
				)
		);

		$cardTypeModel = $this->createStub(CardTypeModel::class);
		$cardTypeModel->expects($this->once())->method('search')->willReturn($cardTypes);

		$service = new CardTypeService(
			$session,
			$cardTypeModel
		);

		self::assertSame($cardTypes, $service->readAll());
	}
}
