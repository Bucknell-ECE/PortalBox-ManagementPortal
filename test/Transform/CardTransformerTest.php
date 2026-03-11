<?php

declare(strict_types=1);

namespace Test\Portalbox\Transform;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Portalbox\Enumeration\CardType;
use Portalbox\Enumeration\ChargePolicy;
use Portalbox\Enumeration\Permission;
use Portalbox\Transform\CardTransformer;
use Portalbox\Type\EquipmentType;
use Portalbox\Type\ProxyCard;
use Portalbox\Type\Role;
use Portalbox\Type\ShutdownCard;
use Portalbox\Type\TrainingCard;
use Portalbox\Type\UserCard;
use Portalbox\Type\User;

final class CardTransformerTest extends TestCase {
	public function testSerializeProxyCard() {
		$proxy_card_id = 123456789;

		$proxy_card = (new ProxyCard())->set_id($proxy_card_id);

		$data = (new CardTransformer())->serialize($proxy_card);
		self::assertIsArray($data);
		self::assertArrayHasKey('id', $data);
		self::assertSame($proxy_card_id, $data['id']);
		self::assertArrayHasKey('card_type_id', $data);
		self::assertSame(CardType::PROXY->value, $data['card_type_id']);
		self::assertArrayHasKey('card_type', $data);
		self::assertSame(CardType::PROXY->name(), $data['card_type']);
		self::assertArrayHasKey('equipment_type_id', $data);
		self::assertEmpty($data['equipment_type_id']);
		self::assertArrayHasKey('equipment_type', $data);
		self::assertEmpty($data['equipment_type']);
		self::assertArrayHasKey('user_id', $data);
		self::assertEmpty($data['user_id']);
		self::assertArrayHasKey('user', $data);
		self::assertEmpty($data['user']);

		$data = (new CardTransformer())->serialize($proxy_card, true);
		self::assertIsArray($data);
		self::assertArrayHasKey('id', $data);
		self::assertSame($proxy_card_id, $data['id']);
		self::assertArrayHasKey('card_type_id', $data);
		self::assertSame(CardType::PROXY->value, $data['card_type_id']);
		self::assertArrayHasKey('card_type', $data);
		self::assertSame(CardType::PROXY->name(), $data['card_type']);
		self::assertArrayHasKey('equipment_type', $data);
		self::assertNull($data['equipment_type']);
		self::assertArrayHasKey('user', $data);
		self::assertNull($data['user']);
	}

	public function testSerializeShutdownCard() {
		$shutdown_card_id = 123456789;

		$shutdown_card = (new ShutdownCard())->set_id($shutdown_card_id);

		$data = (new CardTransformer())->serialize($shutdown_card);
		self::assertIsArray($data);
		self::assertArrayHasKey('id', $data);
		self::assertSame($shutdown_card_id, $data['id']);
		self::assertArrayHasKey('card_type_id', $data);
		self::assertSame(CardType::SHUTDOWN->value, $data['card_type_id']);
		self::assertArrayHasKey('card_type', $data);
		self::assertSame(CardType::SHUTDOWN->name(), $data['card_type']);
		self::assertArrayHasKey('equipment_type_id', $data);
		self::assertEmpty($data['equipment_type_id']);
		self::assertArrayHasKey('equipment_type', $data);
		self::assertEmpty($data['equipment_type']);
		self::assertArrayHasKey('user_id', $data);
		self::assertEmpty($data['user_id']);
		self::assertArrayHasKey('user', $data);
		self::assertEmpty($data['user']);

		$data = (new CardTransformer())->serialize($shutdown_card, true);
		self::assertIsArray($data);
		self::assertArrayHasKey('id', $data);
		self::assertSame($shutdown_card_id, $data['id']);
		self::assertArrayHasKey('card_type_id', $data);
		self::assertSame(CardType::SHUTDOWN->value, $data['card_type_id']);
		self::assertArrayHasKey('card_type', $data);
		self::assertSame(CardType::SHUTDOWN->name(), $data['card_type']);
		self::assertArrayHasKey('equipment_type', $data);
		self::assertNull($data['equipment_type']);
		self::assertArrayHasKey('user', $data);
		self::assertNull($data['user']);
	}

	public function testSerializeTrainingCard() {
		$training_card_id = 123456789;
		$id = 42;
		$name = 'laser scalpel';
		$requires_training = true;
		$charge_rate = "2.50";
		$charge_policy = ChargePolicy::PER_USE;
		$allow_proxy = true;

		$equipment_type = (new EquipmentType())
			->set_id($id)
			->set_name($name)
			->set_requires_training($requires_training)
			->set_charge_rate($charge_rate)
			->set_charge_policy($charge_policy)
			->set_allow_proxy($allow_proxy);

		$trainingCard = (new TrainingCard())
			->set_id($training_card_id)
			->set_equipment_type($equipment_type);

		$data = (new CardTransformer())->serialize($trainingCard);
		self::assertIsArray($data);
		self::assertArrayHasKey('id', $data);
		self::assertSame($training_card_id, $data['id']);
		self::assertArrayHasKey('card_type_id', $data);
		self::assertSame(CardType::TRAINING->value, $data['card_type_id']);
		self::assertArrayHasKey('card_type', $data);
		self::assertSame(CardType::TRAINING->name(), $data['card_type']);
		self::assertArrayHasKey('equipment_type_id', $data);
		self::assertSame($id,$data['equipment_type_id']);
		self::assertArrayHasKey('equipment_type', $data);
		self::assertSame($name, $data['equipment_type']);
		self::assertArrayHasKey('user_id', $data);
		self::assertEmpty($data['user_id']);
		self::assertArrayHasKey('user', $data);
		self::assertEmpty($data['user']);

		$data = (new CardTransformer())->serialize($trainingCard, true);
		self::assertIsArray($data);
		self::assertArrayHasKey('id', $data);
		self::assertSame($training_card_id, $data['id']);
		self::assertArrayHasKey('card_type_id', $data);
		self::assertSame(CardType::TRAINING->value, $data['card_type_id']);
		self::assertArrayHasKey('card_type', $data);
		self::assertSame(CardType::TRAINING->name(), $data['card_type']);
		self::assertArrayHasKey('equipment_type', $data);
		$equipment_type_data = $data['equipment_type'];
		self::assertIsArray($equipment_type_data);
		self::assertEquals($id, $equipment_type_data['id']);
		self::assertArrayHasKey('name', $equipment_type_data);
		self::assertEquals($name, $equipment_type_data['name']);
		self::assertArrayHasKey('requires_training', $equipment_type_data);
		self::assertEquals($requires_training, $equipment_type_data['requires_training']);
		self::assertArrayHasKey('charge_rate', $equipment_type_data);
		self::assertEquals($charge_rate, $equipment_type_data['charge_rate']);
		self::assertArrayHasKey('charge_policy', $equipment_type_data);
		self::assertEquals($charge_policy->value, $equipment_type_data['charge_policy']);
		self::assertArrayHasKey('allow_proxy', $equipment_type_data);
		self::assertEquals($allow_proxy, $equipment_type_data['allow_proxy']);
		self::assertArrayHasKey('user', $data);
		self::assertNull($data['user']);
	}

	public function testSerializeUserCard() {
		$user_card_id = 123456789;
		$role_id = 3;	// default id of system defined admin role
		$role_name = 'administrator';
		$is_system_role = true;
		$description = 'Users with this role have no restrictions.';
		$permissions = [
			Permission::LIST_OWN_EQUIPMENT_AUTHORIZATIONS,
			Permission::LIST_OWN_CARDS
		];

		$role = (new Role())
			->set_id($role_id)
			->set_name($role_name)
			->set_description($description)
			->set_is_system_role($is_system_role)
			->set_permissions($permissions);

		$id = 42;
		$name = 'Tom Egan';
		$email = 'tom@ficticious.tld';
		$comment = 'Test Monkey';
		$is_active = true;
		$equipment_type_id = 1;
		$authorizations = [$equipment_type_id];

		$user = (new User())
			->set_id($id)
			->set_name($name)
			->set_email($email)
			->set_comment($comment)
			->set_is_active($is_active)
			->set_role($role)
			->set_authorizations($authorizations);

		$userCard = (new UserCard())
			->set_id($user_card_id)
			->set_user($user);

		$data = (new CardTransformer())->serialize($userCard);
		self::assertIsArray($data);
		self::assertArrayHasKey('id', $data);
		self::assertSame($user_card_id, $data['id']);
		self::assertArrayHasKey('card_type_id', $data);
		self::assertSame(CardType::USER->value, $data['card_type_id']);
		self::assertArrayHasKey('card_type', $data);
		self::assertSame(CardType::USER->name(), $data['card_type']);
		self::assertArrayHasKey('equipment_type_id', $data);
		self::assertEmpty($data['equipment_type_id']);
		self::assertArrayHasKey('equipment_type', $data);
		self::assertEmpty($data['equipment_type']);
		self::assertArrayHasKey('user_id', $data);
		self::assertSame($id, $data['user_id']);
		self::assertArrayHasKey('user', $data);
		self::assertSame($name, $data['user']);

		$data = (new CardTransformer())->serialize($userCard, true);
		self::assertIsArray($data);
		self::assertArrayHasKey('id', $data);
		self::assertSame($user_card_id, $data['id']);
		self::assertArrayHasKey('card_type_id', $data);
		self::assertSame(CardType::USER->value, $data['card_type_id']);
		self::assertArrayHasKey('card_type', $data);
		self::assertSame(CardType::USER->name(), $data['card_type']);
		self::assertArrayHasKey('equipment_type', $data);
		self::assertNull($data['equipment_type']);
		self::assertArrayHasKey('user', $data);
		$user_data = $data['user'];
		self::assertIsArray($user_data);
		self::assertArrayHasKey('id', $user_data);
		self::assertEquals($id, $user_data['id']);
		self::assertArrayHasKey('name', $user_data);
		self::assertEquals($name, $user_data['name']);
		self::assertArrayHasKey('email', $user_data);
		self::assertEquals($email, $user_data['email']);
		self::assertArrayHasKey('comment', $user_data);
		self::assertEquals($comment, $user_data['comment']);
		self::assertArrayHasKey('is_active', $user_data);
		self::assertEquals($is_active, $user_data['is_active']);
		self::assertArrayHasKey('role', $user_data);
		self::assertIsArray($user_data['role']);
		self::assertArrayHasKey('id', $user_data['role']);
		self::assertEquals($role_id, $user_data['role']['id']);
		self::assertArrayHasKey('name', $user_data['role']);
		self::assertEquals($role_name, $user_data['role']['name']);
		self::assertArrayHasKey('description', $user_data['role']);
		self::assertEquals($description, $user_data['role']['description']);
		self::assertArrayHasKey('system_role', $user_data['role']);
		self::assertEquals($is_system_role, $user_data['role']['system_role']);
		self::assertArrayHasKey('permissions', $user_data['role']);
		self::assertIsArray($user_data['role']['permissions']);
		self::assertCount(2, $user_data['role']['permissions']);
		self::assertContains(Permission::LIST_OWN_EQUIPMENT_AUTHORIZATIONS->value, $user_data['role']['permissions']);
		self::assertContains(Permission::LIST_OWN_CARDS->value, $user_data['role']['permissions']);
		self::assertArrayHasKey('authorizations', $user_data);
		self::assertIsArray($user_data['authorizations']);
		self::assertContains($equipment_type_id, $user_data['authorizations']);
	}
}
