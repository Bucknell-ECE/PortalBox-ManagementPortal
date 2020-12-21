<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Portalbox\Config;

use Portalbox\Entity\CardType;
use Portalbox\Entity\ChargePolicy;
use Portalbox\Entity\EquipmentType;
use Portalbox\Entity\Location;
use Portalbox\Entity\ProxyCard;
use Portalbox\Entity\Role;
use Portalbox\Entity\ShutdownCard;
use Portalbox\Entity\TrainingCard;
use Portalbox\Entity\User;
use Portalbox\Entity\UserCard;

use Portalbox\Model\CardModel;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Model\LocationModel;
use Portalbox\Model\UserModel;

use Portalbox\Query\CardQuery;

final class CardModelTest extends TestCase {
	/**
	 * A user that exists in the db
	 */
	private static $user;

	/**
	 * A location that exists in the db
	 */
	private static $location;

	/**
	 * An equipment type which exists in the db
	 */
	private static $equipment_type;

	/**
	 * The configuration
	 * @var Config
	 */
	private static $config;

	public static function setUpBeforeClass(): void {
		parent::setUp();
		self::$config = Config::config();

		// provision a location in the db
		$model = new LocationModel(self::$config);

		$name = 'Robotics Shop';

		$location = (new Location())
			->set_name($name);

		self::$location = $model->create($location);

		// provision an equipment type in the db
		$model = new EquipmentTypeModel(self::$config);

		$name = 'Floodlight';
		$requires_training = FALSE;
		$charge_policy_id = ChargePolicy::NO_CHARGE;

		$equipment_type = (new EquipmentType())
			->set_name($name)
			->set_requires_training($requires_training)
			->set_charge_policy_id($charge_policy_id);

		self::$equipment_type = $model->create($equipment_type);

		// provision a user in the db
		$model = new UserModel(self::$config);

		$role_id = 3;	// default id of system defined admin role

		$role = (new Role())
			->set_id($role_id);

		$name = 'Tom Egan';
		$email = 'tom@ficticious.tld';
		$comment = 'Test Monkey';
		$active = TRUE;

		$user = (new User())
			->set_name($name)
			->set_email($email)
			->set_comment($comment)
			->set_is_active($active)
			->set_role($role);

		self::$user = $model->create($user);
	}

	public static function tearDownAfterClass() : void {
		// deprovision a location in the db
		$model = new LocationModel(self::$config);
		$model->delete(self::$location->id());

		// deprovision an equipment type in the db
		$model = new EquipmentTypeModel(self::$config);
		$model->delete(self::$equipment_type->id());

		// deprovision a user in the db
		$model = new UserModel(self::$config);
		$model->delete(self::$user->id());
	}

	public function testProxyCardModel(): void {
		$model = new CardModel(self::$config);

		$card_id = 9812347165;

		$card = (new ProxyCard())
			->set_id($card_id);

		$card_as_created = $model->create($card);

		self::assertNotNull($card_as_created);
		self::assertEquals($card_id, $card_as_created->id());
		self::assertEquals(CardType::PROXY, $card_as_created->type_id());

		$card_as_found = $model->read($card_id);

		self::assertNotNull($card_as_found);
		self::assertEquals($card_id, $card_as_found->id());
		self::assertEquals(CardType::PROXY, $card_as_found->type_id());

		$card_as_deleted = $model->delete($card_id);

		self::assertNotNull($card_as_deleted);
		self::assertEquals($card_id, $card_as_deleted->id());
		self::assertEquals(CardType::PROXY, $card_as_deleted->type_id());

		$card_as_not_found = $model->read($card_id);

		self::assertNull($card_as_not_found);
	}

	public function testShutdownCardModel(): void {
		$model = new CardModel(self::$config);

		$card_id = 812347165;

		$card = (new ShutdownCard())
			->set_id($card_id);

		$card_as_created = $model->create($card);

		self::assertNotNull($card_as_created);
		self::assertEquals($card_id, $card_as_created->id());
		self::assertEquals(CardType::SHUTDOWN, $card_as_created->type_id());

		$card_as_found = $model->read($card_id);

		self::assertNotNull($card_as_found);
		self::assertEquals($card_id, $card_as_found->id());
		self::assertEquals(CardType::SHUTDOWN, $card_as_found->type_id());

		$card_as_deleted = $model->delete($card_id);

		self::assertNotNull($card_as_deleted);
		self::assertEquals($card_id, $card_as_deleted->id());
		self::assertEquals(CardType::SHUTDOWN, $card_as_deleted->type_id());

		$card_as_not_found = $model->read($card_id);

		self::assertNull($card_as_not_found);
	}

	public function testTrainingCardModel(): void {
		$model = new CardModel(self::$config);

		$card_id = 812347165;
		$equipment_type_id = self::$equipment_type->id();

		$card = (new TrainingCard())
			->set_id($card_id)
			->set_equipment_type_id($equipment_type_id);

		$card_as_created = $model->create($card);

		self::assertNotNull($card_as_created);
		self::assertEquals($card_id, $card_as_created->id());
		self::assertEquals(CardType::TRAINING, $card_as_created->type_id());
		self::assertEquals($equipment_type_id, $card_as_created->equipment_type_id());

		$card_as_found = $model->read($card_id);

		self::assertNotNull($card_as_found);
		self::assertEquals($card_id, $card_as_found->id());
		self::assertEquals(CardType::TRAINING, $card_as_found->type_id());
		self::assertEquals($equipment_type_id, $card_as_found->equipment_type_id());

		$card_as_deleted = $model->delete($card_id);

		self::assertNotNull($card_as_deleted);
		self::assertEquals($card_id, $card_as_deleted->id());
		self::assertEquals(CardType::TRAINING, $card_as_deleted->type_id());
		self::assertEquals($equipment_type_id, $card_as_deleted->equipment_type_id());

		$card_as_not_found = $model->read($card_id);

		self::assertNull($card_as_not_found);
	}

	public function testUserCardModel(): void {
		$model = new CardModel(self::$config);

		$card_id = 622347165;
		$user_id = self::$user->id();

		$card = (new UserCard())
			->set_id($card_id)
			->set_user_id($user_id);

		$card_as_created = $model->create($card);

		self::assertNotNull($card_as_created);
		self::assertEquals($card_id, $card_as_created->id());
		self::assertEquals(CardType::USER, $card_as_created->type_id());
		self::assertEquals($user_id, $card_as_created->user_id());

		$card_as_found = $model->read($card_id);

		self::assertNotNull($card_as_found);
		self::assertEquals($card_id, $card_as_found->id());
		self::assertEquals(CardType::USER, $card_as_found->type_id());
		self::assertEquals($user_id, $card_as_found->user_id());

		$card_as_deleted = $model->delete($card_id);

		self::assertNotNull($card_as_deleted);
		self::assertEquals($card_id, $card_as_deleted->id());
		self::assertEquals(CardType::USER, $card_as_deleted->type_id());
		self::assertEquals($user_id, $card_as_deleted->user_id());

		$card_as_not_found = $model->read($card_id);

		self::assertNull($card_as_not_found);
	}

	public function testSearch(): void {
		$model = new CardModel(self::$config);

		$card_id = 622347165;
		$user_id = self::$user->id();

		$card = (new UserCard())
			->set_id($card_id)
			->set_user_id($user_id);

		$user_card_1 = $model->create($card);

		$card_id = 622347166;
		$user_id = self::$user->id();

		$card = (new UserCard())
			->set_id($card_id)
			->set_user_id($user_id);

		$user_card_2 = $model->create($card);

		$card_id = 812347165;
		$equipment_type_id = self::$equipment_type->id();

		$card = (new TrainingCard())
			->set_id($card_id)
			->set_equipment_type_id($equipment_type_id);

		$training_card = $model->create($card);

		$card_id = 47165542;

		$card = (new ShutdownCard())
			->set_id($card_id);

		$shutdown_card = $model->create($card);

		$cards = array_map(function ($card) {
			return $card->id();
		}, $model->search());
		self::assertContains($user_card_1->id(), $cards);
		self::assertContains($user_card_2->id(), $cards);
		self::assertContains($training_card->id(), $cards);
		self::assertContains($shutdown_card->id(), $cards);

		$query = new CardQuery();
		$cards = array_map(function ($card) {
			return $card->id();
		}, $model->search($query));
		self::assertContains($user_card_1->id(), $cards);
		self::assertContains($user_card_2->id(), $cards);
		self::assertContains($training_card->id(), $cards);
		self::assertContains($shutdown_card->id(), $cards);

		$query = (new CardQuery())
			->set_user_id($user_id);
		$cards = array_map(function ($card) {
			return $card->id();
		}, $model->search($query));
		self::assertContains($user_card_1->id(), $cards);
		self::assertContains($user_card_2->id(), $cards);
		self::assertNotContains($training_card->id(), $cards);
		self::assertNotContains($shutdown_card->id(), $cards);

		$query = (new CardQuery())
			->set_equipment_type_id($equipment_type_id);
		$cards = array_map(function ($card) {
			return $card->id();
		}, $model->search($query));
		self::assertNotContains($user_card_1->id(), $cards);
		self::assertNotContains($user_card_2->id(), $cards);
		self::assertContains($training_card->id(), $cards);
		self::assertNotContains($shutdown_card->id(), $cards);

		$model->delete($user_card_1->id());
		$model->delete($user_card_2->id());
		$model->delete($training_card->id());
		$model->delete($shutdown_card->id());
	}
}