<?php

declare(strict_types=1);

namespace Test\Portalbox\Service;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Portalbox\Entity\CardType;
use Portalbox\Entity\EquipmentType;
use Portalbox\Entity\Permission;
use Portalbox\Entity\ProxyCard;
use Portalbox\Entity\Role;
use Portalbox\Entity\ShutdownCard;
use Portalbox\Entity\TrainingCard;
use Portalbox\Entity\User;
use Portalbox\Entity\UserCard;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Exception\NotFoundException;
use Portalbox\Model\CardModel;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Model\UserModel;
use Portalbox\Query\CardQuery;
use Portalbox\Service\CardService;
use Portalbox\Session\SessionInterface;

final class CardServiceTest extends TestCase {
	#region test create()

	public function testCreateThrowsWhenNotAuthenticated() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$cardModel = $this->createStub(CardModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new CardService(
			$session,
			$cardModel,
			$equipmentTypeModel,
			$userModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(CardService::ERROR_UNAUTHENTICATED_CREATE);
		$service->create('not a file path');
	}

	public function testCreateThrowsWhenNotAuthorized() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$cardModel = $this->createStub(CardModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new CardService(
			$session,
			$cardModel,
			$equipmentTypeModel,
			$userModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(CardService::ERROR_UNAUTHORIZED_CREATE);
		$service->create('not a file path');
	}

	public function testCreateThrowsWhenFileIsNotReadable() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_CARD])
				)
		);

		$cardModel = $this->createStub(CardModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new CardService(
			$session,
			$cardModel,
			$equipmentTypeModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(CardService::ERROR_INVALID_CARD_DATA);
		// PHP warning is intentionally suppressed in next line for testing
		@$service->create('file_does_not_exist.json');
	}

	public function testCreateThrowsWhenDataIsNotArray() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_CARD])
				)
		);

		$cardModel = $this->createStub(CardModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new CardService(
			$session,
			$cardModel,
			$equipmentTypeModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(CardService::ERROR_INVALID_CARD_DATA);
		$service->create(realpath(__DIR__ . '/CardServiceTestData/CreateThrowsWhenDataIsNotArray.json'));
	}

	public function testCreateThrowsWhenIdIsNotSpecified() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_CARD])
				)
		);

		$cardModel = $this->createStub(CardModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new CardService(
			$session,
			$cardModel,
			$equipmentTypeModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(CardService::ERROR_CARD_ID_IS_REQUIRED);
		$service->create(realpath(__DIR__ . '/CardServiceTestData/CreateThrowsWhenIdIsNotSpecified.json'));
	}

	public function testCreateThrowsWhenIdIsInvalid() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_CARD])
				)
		);

		$cardModel = $this->createStub(CardModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new CardService(
			$session,
			$cardModel,
			$equipmentTypeModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(CardService::ERROR_CARD_ID_IS_REQUIRED);
		$service->create(realpath(__DIR__ . '/CardServiceTestData/CreateThrowsWhenIdIsInvalid.json'));
	}

	public function testCreateThrowsWhenTypeIsNotSpecified() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_CARD])
				)
		);

		$cardModel = $this->createStub(CardModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new CardService(
			$session,
			$cardModel,
			$equipmentTypeModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(CardService::ERROR_CARD_TYPE_IS_REQUIRED);
		$service->create(realpath(__DIR__ . '/CardServiceTestData/CreateThrowsWhenTypeIsNotSpecified.json'));
	}

	public function testCreateThrowsWhenTypeIsInvalid() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_CARD])
				)
		);

		$cardModel = $this->createStub(CardModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new CardService(
			$session,
			$cardModel,
			$equipmentTypeModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(CardService::ERROR_CARD_TYPE_IS_INVALID);
		$service->create(realpath(__DIR__ . '/CardServiceTestData/CreateThrowsWhenTypeIsInvalid.json'));
	}

	public function testCreateThrowsWhenUserIsNotSpecified() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_CARD])
				)
		);

		$cardModel = $this->createStub(CardModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new CardService(
			$session,
			$cardModel,
			$equipmentTypeModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(CardService::ERROR_USER_ID_IS_REQUIRED);
		$service->create(realpath(__DIR__ . '/CardServiceTestData/CreateThrowsWhenUserIsNotSpecified.json'));
	}

	public function testCreateThrowsWhenUserIdIsInvalid() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_CARD])
				)
		);

		$cardModel = $this->createStub(CardModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new CardService(
			$session,
			$cardModel,
			$equipmentTypeModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(CardService::ERROR_USER_ID_IS_REQUIRED);
		$service->create(realpath(__DIR__ . '/CardServiceTestData/CreateThrowsWhenUserIdIsInvalid.json'));
	}

	public function testCreateThrowsWhenUserDoesNotExist() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_CARD])
				)
		);

		$cardModel = $this->createStub(CardModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$userModel = $this->createStub(UserModel::class);
		$userModel->method('read')->willReturn(null);

		$service = new CardService(
			$session,
			$cardModel,
			$equipmentTypeModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(CardService::ERROR_USER_ID_IS_INVALID);
		$service->create(realpath(__DIR__ . '/CardServiceTestData/CreateThrowsWhenUserDoesNotExist.json'));
	}

	public function testCreateThrowsWhenEquipmentTypeIsNotSpecified() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_CARD])
				)
		);

		$cardModel = $this->createStub(CardModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new CardService(
			$session,
			$cardModel,
			$equipmentTypeModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(CardService::ERROR_EQUIPMENT_TYPE_ID_IS_REQUIRED);
		$service->create(realpath(__DIR__ . '/CardServiceTestData/CreateThrowsWhenEquipmentTypeIsNotSpecified.json'));
	}

	public function testCreateThrowsWhenEquipmentTypeIdIsInvalid() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_CARD])
				)
		);

		$cardModel = $this->createStub(CardModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new CardService(
			$session,
			$cardModel,
			$equipmentTypeModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(CardService::ERROR_EQUIPMENT_TYPE_ID_IS_REQUIRED);
		$service->create(realpath(__DIR__ . '/CardServiceTestData/CreateThrowsWhenEquipmentTypeIdIsInvalid.json'));
	}

	public function testCreateThrowsWhenEquipmentTypeDoesNotExist() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_CARD])
				)
		);

		$cardModel = $this->createStub(CardModel::class);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('read')->willReturn(null);

		$userModel = $this->createStub(UserModel::class);

		$service = new CardService(
			$session,
			$cardModel,
			$equipmentTypeModel,
			$userModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(CardService::ERROR_EQUIPMENT_TYPE_ID_IS_INVALID);
		$service->create(realpath(__DIR__ . '/CardServiceTestData/CreateThrowsWhenEquipmentTypeDoesNotExist.json'));
	}

	public function testCreateUserCardSuccess() {
		// some data that matches values in the json file
		$id = 123456789;
		$user_id = 12;

		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_CARD])
				)
		);

		$cardModel = $this->createStub(CardModel::class);
		$cardModel->expects($this->once())->method('create')->with(
			$this->callback(
				fn($card) =>
					$card->type_id() === CardType::USER
					&& $card->id() === $id
					&& $card->user_id() === $user_id
			)
		)
		->willReturnArgument(0);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);

		$userModel = $this->createStub(UserModel::class);
		$userModel->method('read')->willReturn(new User());

		$service = new CardService(
			$session,
			$cardModel,
			$equipmentTypeModel,
			$userModel
		);

		$card = $service->create(realpath(__DIR__ . '/CardServiceTestData/CreateUserCardSuccess.json'));

		self::assertInstanceOf(UserCard::class, $card);
		self::assertSame($id, $card->id());
		self::assertSame($user_id, $card->user_id());
	}

	public function testCreateTrainingCardSuccess() {
		// some data that matches values in the json file
		$id = 123456789;
		$equipment_type_id = 12;

		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_CARD])
				)
		);

		$cardModel = $this->createStub(CardModel::class);
		$cardModel->expects($this->once())->method('create')->with(
			$this->callback(
				fn($card) =>
					$card->type_id() === CardType::TRAINING
					&& $card->id() === $id
					&& $card->equipment_type_id() === $equipment_type_id
			)
		)
		->willReturnArgument(0);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$equipmentTypeModel->method('read')->willReturn(new EquipmentType());

		$userModel = $this->createStub(UserModel::class);

		$service = new CardService(
			$session,
			$cardModel,
			$equipmentTypeModel,
			$userModel
		);

		$card = $service->create(realpath(__DIR__ . '/CardServiceTestData/CreateTrainingCardSuccess.json'));

		self::assertInstanceOf(TrainingCard::class, $card);
		self::assertSame($id, $card->id());
		self::assertSame($equipment_type_id, $card->equipment_type_id());
	}

	public function testCreateShutdownCardSuccess() {
		// some data that matches values in the json file
		$id = 123456789;

		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_CARD])
				)
		);

		$cardModel = $this->createStub(CardModel::class);
		$cardModel->expects($this->once())->method('create')->with(
			$this->callback(
				fn($card) =>
					$card->type_id() === CardType::SHUTDOWN
					&& $card->id() === $id
			)
		)
		->willReturnArgument(0);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new CardService(
			$session,
			$cardModel,
			$equipmentTypeModel,
			$userModel
		);

		$card = $service->create(realpath(__DIR__ . '/CardServiceTestData/CreateShutdownCardSuccess.json'));

		self::assertInstanceOf(ShutdownCard::class, $card);
		self::assertSame($id, $card->id());
	}

	public function testCreateProxyCardSuccess() {
		// some data that matches values in the json file
		$id = 123456789;

		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_CARD])
				)
		);

		$cardModel = $this->createStub(CardModel::class);
		$cardModel->expects($this->once())->method('create')->with(
			$this->callback(
				fn($card) =>
					$card->type_id() === CardType::PROXY
					&& $card->id() === $id
			)
		)
		->willReturnArgument(0);

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new CardService(
			$session,
			$cardModel,
			$equipmentTypeModel,
			$userModel
		);

		$card = $service->create(realpath(__DIR__ . '/CardServiceTestData/CreateProxyCardSuccess.json'));

		self::assertInstanceOf(ProxyCard::class, $card);
		self::assertSame($id, $card->id());
	}

	#endregion test create()

	#region test read()

	public function testReadThrowsWhenNotAuthenticated() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$cardModel = $this->createStub(CardModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new CardService(
			$session,
			$cardModel,
			$equipmentTypeModel,
			$userModel
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
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new CardService(
			$session,
			$cardModel,
			$equipmentTypeModel,
			$userModel
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

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new CardService(
			$session,
			$cardModel,
			$equipmentTypeModel,
			$userModel
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

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new CardService(
			$session,
			$cardModel,
			$equipmentTypeModel,
			$userModel
		);

		self::assertSame($card, $service->read(123456789));
	}

	#endregion test read()

	#region test readAll()

	public function testReadAllThrowsWhenNotAuthenticated() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$cardModel = $this->createStub(CardModel::class);
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new CardService(
			$session,
			$cardModel,
			$equipmentTypeModel,
			$userModel
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
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new CardService(
			$session,
			$cardModel,
			$equipmentTypeModel,
			$userModel
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
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new CardService(
			$session,
			$cardModel,
			$equipmentTypeModel,
			$userModel
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
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new CardService(
			$session,
			$cardModel,
			$equipmentTypeModel,
			$userModel
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
		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new CardService(
			$session,
			$cardModel,
			$equipmentTypeModel,
			$userModel
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

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new CardService(
			$session,
			$cardModel,
			$equipmentTypeModel,
			$userModel
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

		$equipmentTypeModel = $this->createStub(EquipmentTypeModel::class);
		$userModel = $this->createStub(UserModel::class);

		$service = new CardService(
			$session,
			$cardModel,
			$equipmentTypeModel,
			$userModel
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
