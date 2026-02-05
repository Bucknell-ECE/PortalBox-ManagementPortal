<?php

declare(strict_types=1);

namespace Test\Portalbox\Transform;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use PortalBox\Config;
use Portalbox\Enumeration\ChargePolicy;
use PortalBox\Enumeration\Permission;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Transform\UserTransformer;
use Portalbox\Type\EquipmentType;
use PortalBox\Type\Role;
use PortalBox\Type\User;

final class UserTransformerTest extends TestCase {
	/**
	 * An equipment type which exists in the db
	 */
	private static $type;

	public static function setUpBeforeClass(): void {
		parent::setUpBeforeClass();

		// provision an equipment type in the db
		$model = new EquipmentTypeModel(Config::config());

		$name = 'Floodlight';
		$requires_training = false;
		$charge_policy = ChargePolicy::NO_CHARGE;

		$type = (new EquipmentType())
			->set_name($name)
			->set_requires_training($requires_training)
			->set_charge_policy($charge_policy)
			->set_allow_proxy(false);

		self::$type = $model->create($type);
	}

	public static function tearDownAfterClass(): void {
		// deprovision an equipment type in the db
		$model = new EquipmentTypeModel(Config::config());
		$model->delete(self::$type->id());

		parent::tearDownAfterClass();
	}

	public function testSerialize(): void {
		$transformer = new UserTransformer();

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
		$authorizations = [self::$type->id()];

		$user = (new User())
			->set_id($id)
			->set_name($name)
			->set_email($email)
			->set_comment($comment)
			->set_is_active($is_active)
			->set_role($role)
			->set_authorizations($authorizations);

		$data = $transformer->serialize($user, true);

		self::assertNotNull($data);
		self::assertArrayHasKey('id', $data);
		self::assertEquals($id, $data['id']);
		self::assertArrayHasKey('name', $data);
		self::assertEquals($name, $data['name']);
		self::assertArrayHasKey('email', $data);
		self::assertEquals($email, $data['email']);
		self::assertArrayHasKey('comment', $data);
		self::assertEquals($comment, $data['comment']);
		self::assertArrayHasKey('is_active', $data);
		self::assertEquals($is_active, $data['is_active']);
		self::assertArrayHasKey('role', $data);
		self::assertIsArray($data['role']);
		self::assertArrayHasKey('id', $data['role']);
		self::assertEquals($role_id, $data['role']['id']);
		self::assertArrayHasKey('name', $data['role']);
		self::assertEquals($role_name, $data['role']['name']);
		self::assertArrayHasKey('description', $data['role']);
		self::assertEquals($description, $data['role']['description']);
		self::assertArrayHasKey('system_role', $data['role']);
		self::assertEquals($is_system_role, $data['role']['system_role']);
		self::assertArrayHasKey('permissions', $data['role']);
		self::assertIsArray($data['role']['permissions']);
		self::assertCount(2, $data['role']['permissions']);
		self::assertContains(Permission::LIST_OWN_EQUIPMENT_AUTHORIZATIONS->value, $data['role']['permissions']);
		self::assertContains(Permission::LIST_OWN_CARDS->value, $data['role']['permissions']);
		self::assertArrayHasKey('authorizations', $data);
		self::assertIsArray($data['authorizations']);
		self::assertContains(self::$type->id(), $data['authorizations']);
	}
}
