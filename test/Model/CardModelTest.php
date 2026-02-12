<?php

declare(strict_types=1);

namespace Test\Portalbox\Model;

use PHPUnit\Framework\TestCase;
use Portalbox\Config;
use Portalbox\Enumeration\CardType;
use Portalbox\Enumeration\ChargePolicy;
use Portalbox\Model\CardModel;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Model\UserModel;
use Portalbox\Query\CardQuery;
use Portalbox\Type\EquipmentType;
use Portalbox\Type\ProxyCard;
use Portalbox\Type\Role;
use Portalbox\Type\ShutdownCard;
use Portalbox\Type\TrainingCard;
use Portalbox\Type\User;
use Portalbox\Type\UserCard;

final class CardModelTest extends TestCase {
	/**
	 * A user that exists in the db
	 */
	private static User $user;

	/**
	 * An equipment type which exists in the db
	 */
	private static EquipmentType $equipment_type;

	/**
	 * The configuration
	 */
	private static Config $config;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();
		self::$config = Config::config();

		// provision an equipment type in the db
		$model = new EquipmentTypeModel(self::$config);

		$name = 'Floodlight';
		$requires_training = false;
		$charge_policy = ChargePolicy::NO_CHARGE;

		$equipment_type = (new EquipmentType())
			->set_name($name)
			->set_requires_training($requires_training)
			->set_charge_policy($charge_policy)
			->set_allow_proxy(false);

		self::$equipment_type = $model->create($equipment_type);

		// provision a user in the db
		$model = new UserModel(self::$config);

		$role_id = 3;	// default id of system defined admin role

		$role = (new Role())
			->set_id($role_id);

		$name = 'Tom Egan';
		$email = 'tom@ficticious.tld';
		$comment = 'Test Monkey';
		$active = true;

		$user = (new User())
			->set_name($name)
			->set_email($email)
			->set_comment($comment)
			->set_is_active($active)
			->set_role($role);

		self::$user = $model->create($user);
	}

	public static function tearDownAfterClass(): void {
		// deprovision an equipment type in the db
		$model = new EquipmentTypeModel(self::$config);
		$model->delete(self::$equipment_type->id());

		// deprovision a user in the db
		$model = new UserModel(self::$config);
		$model->delete(self::$user->id());

		parent::tearDownAfterClass();
	}

	public function testCreateReadDelete_ProxyCard(): void {
		$model = new CardModel(self::$config);

		$card_id = 9812347165;

		$card = $model->create(
			(new ProxyCard())
				->set_id($card_id)
		);

		self::assertInstanceOf(ProxyCard::class, $card);
		self::assertSame($card_id, $card->id());
		self::assertSame(CardType::PROXY, $card->type());

		$card = $model->read($card_id);

		self::assertInstanceOf(ProxyCard::class, $card);
		self::assertSame($card_id, $card->id());
		self::assertSame(CardType::PROXY, $card->type());

		$card = $model->delete($card_id);

		self::assertInstanceOf(ProxyCard::class, $card);
		self::assertSame($card_id, $card->id());
		self::assertSame(CardType::PROXY, $card->type());

		self::assertNull($model->read($card_id));
	}

	public function testCreateReadDelete_ShutdownCard(): void {
		$model = new CardModel(self::$config);

		$card_id = 812347165;

		$card = $model->create(
			(new ShutdownCard())
				->set_id($card_id)
		);

		self::assertInstanceOf(ShutdownCard::class, $card);
		self::assertSame($card_id, $card->id());
		self::assertSame(CardType::SHUTDOWN, $card->type());

		$card = $model->read($card_id);

		self::assertInstanceOf(ShutdownCard::class, $card);
		self::assertSame($card_id, $card->id());
		self::assertSame(CardType::SHUTDOWN, $card->type());

		$card = $model->delete($card_id);

		self::assertInstanceOf(ShutdownCard::class, $card);
		self::assertSame($card_id, $card->id());
		self::assertSame(CardType::SHUTDOWN, $card->type());

		self::assertNull($model->read($card_id));
	}

	public function testCreateReadDelete_TrainingCard(): void {
		$model = new CardModel(self::$config);

		$card_id = 812347165;
		$equipment_type_id = self::$equipment_type->id();

		$card = (new TrainingCard())
			->set_id($card_id)
			->set_equipment_type_id($equipment_type_id);

		$card = $model->create($card);

		self::assertInstanceOf(TrainingCard::class, $card);
		self::assertSame($card_id, $card->id());
		self::assertSame(CardType::TRAINING, $card->type());
		self::assertSame($equipment_type_id, $card->equipment_type_id());

		$card = $model->read($card_id);

		self::assertInstanceOf(TrainingCard::class, $card);
		self::assertSame($card_id, $card->id());
		self::assertSame(CardType::TRAINING, $card->type());
		self::assertSame($equipment_type_id, $card->equipment_type_id());

		$card = $model->delete($card_id);

		self::assertInstanceOf(TrainingCard::class, $card);
		self::assertSame($card_id, $card->id());
		self::assertSame(CardType::TRAINING, $card->type());
		self::assertSame($equipment_type_id, $card->equipment_type_id());

		self::assertNull($model->read($card_id));
	}

	public function testCreateReadDelete_UserCard(): void {
		$model = new CardModel(self::$config);

		$card_id = 622347165;
		$user_id = self::$user->id();

		$card = $model->create(
			(new UserCard())
				->set_id($card_id)
				->set_user_id($user_id)
		);

		self::assertInstanceOf(UserCard::class, $card);
		self::assertSame($card_id, $card->id());
		self::assertSame(CardType::USER, $card->type());
		self::assertSame($user_id, $card->user_id());

		$card = $model->read($card_id);

		self::assertInstanceOf(UserCard::class, $card);
		self::assertSame($card_id, $card->id());
		self::assertSame(CardType::USER, $card->type());
		self::assertSame($user_id, $card->user_id());

		$card = $model->delete($card_id);

		self::assertInstanceOf(UserCard::class, $card);
		self::assertSame($card_id, $card->id());
		self::assertSame(CardType::USER, $card->type());
		self::assertSame($user_id, $card->user_id());

		self::assertNull($model->read($card_id));
	}

	public function testSearch(): void {
		$model = new CardModel(self::$config);

		$userCard1Id = 622347165;
		$userCard2Id = 622347166;
		$trainingCardId = 812347165;
		$shutdownCardId = 47165542;

		$model->create(
			(new UserCard())
				->set_id($userCard1Id)
				->set_user_id(self::$user->id())
		);

		$model->create(
			(new UserCard())
				->set_id($userCard2Id)
				->set_user_id(self::$user->id())
		);

		$model->create(
			(new TrainingCard())
				->set_id($trainingCardId)
				->set_equipment_type_id(self::$equipment_type->id())
		);

		$model->create(
			(new ShutdownCard())
				->set_id($shutdownCardId)
		);

		$cards = array_map(fn($card) => $card->id(), $model->search());
		self::assertContains($userCard1Id, $cards);
		self::assertContains($userCard2Id, $cards);
		self::assertContains($trainingCardId, $cards);
		self::assertContains($shutdownCardId, $cards);

		$query = new CardQuery();
		$cards = array_map(fn($card) => $card->id(), $model->search($query));
		self::assertContains($userCard1Id, $cards);
		self::assertContains($userCard2Id, $cards);
		self::assertContains($trainingCardId, $cards);
		self::assertContains($shutdownCardId, $cards);

		$query = (new CardQuery())
			->set_user_id(self::$user->id());
		$cards = array_map(fn($card) => $card->id(), $model->search($query));
		self::assertContains($userCard1Id, $cards);
		self::assertContains($userCard2Id, $cards);
		self::assertNotContains($trainingCardId, $cards);
		self::assertNotContains($shutdownCardId, $cards);

		$query = (new CardQuery())
			->set_equipment_type_id(self::$equipment_type->id());
		$cards = array_map(fn($card) => $card->id(), $model->search($query));
		self::assertNotContains($userCard1Id, $cards);
		self::assertNotContains($userCard2Id, $cards);
		self::assertContains($trainingCardId, $cards);
		self::assertNotContains($shutdownCardId, $cards);

		$model->delete($userCard1Id);
		$model->delete($userCard2Id);
		$model->delete($trainingCardId);
		$model->delete($shutdownCardId);
	}
}
