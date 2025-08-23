<?php

declare(strict_types=1);

namespace Test\Portalbox\Service;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Portalbox\Entity\TrainingCard;
use Portalbox\Entity\Permission;
use Portalbox\Entity\Role;
use Portalbox\Entity\User;
use Portalbox\Entity\UserCard;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Exception\NotFoundException;
use Portalbox\Model\CardModel;
use Portalbox\Query\CardQuery;
use Portalbox\Service\CardService;
use Portalbox\Session\SessionInterface;

final class CardServiceTest extends TestCase {
	#region test read()

	public function testReadThrowsWhenNotAuthenticated() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$cardModel = $this->createStub(CardModel::class);

		$service = new CardService(
			$session,
			$cardModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(CardService::ERROR_UNAUTHENTICATED_READ);
		$service->read(123456789);
	}

	public function testReadThrowsWhenNotAuthorized() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$cardModel = $this->createStub(CardModel::class);

		$service = new CardService(
			$session,
			$cardModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(CardService::ERROR_UNAUTHORIZED_READ);
		$service->read(123456789);
	}

	public function testReadThrowsWhenCardDoesNotExist() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::READ_CARD])
				)
		);

		$cardModel = $this->createStub(CardModel::class);
		$cardModel->method('read')->willReturn(null);

		$service = new CardService(
			$session,
			$cardModel
		);

		self::expectException(NotFoundException::class);
		self::expectExceptionMessage(CardService::ERROR_CARD_NOT_FOUND);
		$service->read(123456789);
	}

	public function testReadSuccess() {
		$card = new TrainingCard();

		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::READ_CARD])
				)
		);

		$cardModel = $this->createStub(CardModel::class);
		$cardModel->method('read')->willReturn($card);

		$service = new CardService(
			$session,
			$cardModel
		);

		self::assertSame($card, $service->read(123456789));
	}

	#endregion test read()

	#region test readAll()

	public function testReadAllThrowsWhenNotAuthenticated() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$cardModel = $this->createStub(CardModel::class);

		$service = new CardService(
			$session,
			$cardModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(CardService::ERROR_UNAUTHENTICATED_READ);
		$service->readAll([]);
	}

	public function testReadAllThrowsWhenNotEquipmentTypeFilterIsNotInteger() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$cardModel = $this->createStub(CardModel::class);

		$service = new CardService(
			$session,
			$cardModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(CardService::ERROR_EQUIPMENT_TYPE_FILTER_MUST_BE_INT);
		$service->readAll(['equipment_type_id' => 'meh']);
	}

	public function testReadAllThrowsWhenNotUserFilterIsNotInteger() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$cardModel = $this->createStub(CardModel::class);

		$service = new CardService(
			$session,
			$cardModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(CardService::ERROR_USER_FILTER_MUST_BE_INT);
		$service->readAll(['user_id' => 'meh']);
	}

	public function testReadAllThrowsWhenNotAuthorized() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$cardModel = $this->createStub(CardModel::class);

		$service = new CardService(
			$session,
			$cardModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(CardService::ERROR_UNAUTHORIZED_READ);
		$service->readAll([]);
	}

	public function testReadAllThrowsWhenUserTriesToReadOtherUsersCards() {
		$authenticatedUserId = 123;

		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_id($authenticatedUserId)
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::LIST_OWN_CARDS])
				)
		);

		$cardModel = $this->createStub(CardModel::class);

		$service = new CardService(
			$session,
			$cardModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(CardService::ERROR_UNAUTHORIZED_READ);
		$service->readAll(['user_id' => (string)($authenticatedUserId + 1)]);
	}

	public function testReadAllSuccessForUserReadingOwnCards() {
		$authenticatedUserId = 123;
		$cards = [
			new UserCard()
		];

		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_id($authenticatedUserId)
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::LIST_OWN_CARDS])
				)
		);

		$cardModel = $this->createStub(CardModel::class);
		$cardModel->expects($this->once())->method('search')->with(
			$this->callback(
				fn(CardQuery $query) => $query->user_id() === $authenticatedUserId
			)
		)
		->willReturn($cards);

		$service = new CardService(
			$session,
			$cardModel
		);

		self::assertSame(
			$cards,
			$service->readAll(['user_id' => (string)($authenticatedUserId)])
		);
	}

	/**
	 * @dataProvider getReadAllFilters
	 */
	public function testReadAllSuccessForAllCards(
		$filters,
		$user_id,
		$equipment_type_id
	) {
		$cards = [
			new UserCard()
		];

		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::LIST_CARDS])
				)
		);

		$cardModel = $this->createStub(CardModel::class);
		$cardModel->expects($this->once())->method('search')->with(
			$this->callback(
				fn(CardQuery $query) =>
					$query->user_id() === $user_id
					&& $query->equipment_type_id() === $equipment_type_id
			)
		)
		->willReturn($cards);

		$service = new CardService(
			$session,
			$cardModel
		);

		self::assertSame(
			$cards,
			$service->readAll($filters)
		);
	}

	public static function getReadAllFilters(): iterable {
		yield [
			[],
			NULL,
			NULL
		];

		yield [
			['user_id' => '123'],
			123,
			NULL
		];

		yield [
			['equipment_type_id' => '12'],
			NULL,
			12,
		];
	}

	#endregion test readAll()
}
