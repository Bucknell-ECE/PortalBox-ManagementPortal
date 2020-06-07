<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use PortalBox\Config;

use Portalbox\Entity\ChargePolicy;
use Portalbox\Entity\EquipmentType;
use PortalBox\Entity\Permission;
use PortalBox\Entity\Role;
use PortalBox\Entity\User;

use Portalbox\Model\EquipmentTypeModel;

use Portalbox\Transform\UserTransformer;

final class UserTransformerTest extends TestCase {
		/**
	 * An equipment type which exists in the db
	 */
	private static $type;

	public static function setUpBeforeClass(): void {
		parent::setUp();

		// provision an equipment type in the db
		$model = new EquipmentTypeModel(Config::config());

		$name = 'Floodlight';
		$requires_training = FALSE;
		$charge_policy_id = ChargePolicy::NO_CHARGE;

		$type = (new EquipmentType())
			->set_name($name)
			->set_requires_training($requires_training)
			->set_charge_policy_id($charge_policy_id);

		self::$type = $model->create($type);
	}

	public static function tearDownAfterClass() : void {
		// deprovision an equipment type in the db
		$model = new EquipmentTypeModel(Config::config());
		$model->delete(self::$type->id());
	}

	public function testDeserialize(): void {
		$transformer = new UserTransformer();

		$id = 42;
		$role_id = 3;	// default id of system defined admin role
		$name = 'Tom Egan';
		$email = 'tom@ficticious.tld';
		$comment = 'Test Monkey';
		$is_active = TRUE;
		$authorizations = [self::$type->id()];
		$num_authorizations = count($authorizations);

		$data = [
			'id' => $id,
			'role_id' => $role_id,
			'name' => $name,
			'email' => $email,
			'comment' => $comment,
			'is_active' => $is_active,
			'authorizations' => $authorizations
		];

		$user = $transformer->deserialize($data);

		self::assertNotNull($user);
		self::assertNull($user->id());
		self::assertEquals($name, $user->name());
		self::assertEquals($email, $user->email());
		self::assertEquals($comment, $user->comment());
		self::assertEquals($is_active, $user->is_active());
		$role = $user->role();
		self::assertNotNull($role);
		self::assertEquals($role_id, $role->id());
		self::assertIsIterable($user->authorizations());
		self::assertCount($num_authorizations, $user->authorizations());
		self::assertContains(self::$type->id(), $user->authorizations());
	}

	public function testDeserializeInvalidDataName(): void {
		$transformer = new UserTransformer();

		$id = 42;
		$role_id = 3;	// default id of system defined admin role
		$email = 'tom@ficticious.tld';
		$comment = 'Test Monkey';
		$is_active = TRUE;

		$data = [
			'id' => $id,
			'role_id' => $role_id,
			'email' => $email,
			'comment' => $comment,
			'is_active' => $is_active
		];

		$this->expectException(InvalidArgumentException::class);
		$user = $transformer->deserialize($data);
	}

	public function testDeserializeInvalidDataMissingRoleId(): void {
		$transformer = new UserTransformer();

		$id = 42;
		$name = 'Tom Egan';
		$email = 'tom@ficticious.tld';
		$comment = 'Test Monkey';
		$is_active = TRUE;

		$data = [
			'id' => $id,
			'name' => $name,
			'email' => $email,
			'comment' => $comment,
			'is_active' => $is_active
		];

		$this->expectException(InvalidArgumentException::class);
		$user = $transformer->deserialize($data);
	}

	public function testDeserializeInvalidDataRoleId(): void {
		$transformer = new UserTransformer();

		$id = 42;
		$role_id = 0;
		$name = 'Tom Egan';
		$email = 'tom@ficticious.tld';
		$comment = 'Test Monkey';
		$is_active = TRUE;

		$data = [
			'id' => $id,
			'role_id' => $role_id,
			'name' => $name,
			'email' => $email,
			'comment' => $comment,
			'is_active' => $is_active
		];

		$this->expectException(InvalidArgumentException::class);
		$user = $transformer->deserialize($data);
	}

	public function testDeserializeInvalidDataEmail(): void {
		$transformer = new UserTransformer();

		$id = 42;
		$role_id = 3;	// default id of system defined admin role
		$name = 'Tom Egan';
		$comment = 'Test Monkey';
		$is_active = TRUE;

		$data = [
			'id' => $id,
			'role_id' => $role_id,
			'name' => $name,
			'comment' => $comment,
			'is_active' => $is_active
		];

		$this->expectException(InvalidArgumentException::class);
		$user = $transformer->deserialize($data);
	}

	public function testDeserializeInvalidDataIsActive(): void {
		$transformer = new UserTransformer();

		$id = 42;
		$role_id = 3;	// default id of system defined admin role
		$name = 'Tom Egan';
		$email = 'tom@ficticious.tld';
		$comment = 'Test Monkey';

		$data = [
			'id' => $id,
			'role_id' => $role_id,
			'name' => $name,
			'email' => $email,
			'comment' => $comment
		];

		$this->expectException(InvalidArgumentException::class);
		$user = $transformer->deserialize($data);
	}

	public function testDeserializeInvalidDataAuthorizationsType(): void {
		$transformer = new UserTransformer();

		$id = 42;
		$role_id = 3;	// default id of system defined admin role
		$name = 'Tom Egan';
		$email = 'tom@ficticious.tld';
		$comment = 'Test Monkey';
		$is_active = TRUE;
		$authorizations = true;

		$data = [
			'id' => $id,
			'role_id' => $role_id,
			'name' => $name,
			'email' => $email,
			'comment' => $comment,
			'is_active' => $is_active,
			'authorizations' => $authorizations
		];

		$this->expectException(InvalidArgumentException::class);
		$user = $transformer->deserialize($data);
	}

	public function testDeserializeInvalidDataAuthorizationsBadId(): void {
		$transformer = new UserTransformer();

		$id = 42;
		$role_id = 3;	// default id of system defined admin role
		$name = 'Tom Egan';
		$email = 'tom@ficticious.tld';
		$comment = 'Test Monkey';
		$is_active = TRUE;
		$authorizations = [-1];

		$data = [
			'id' => $id,
			'role_id' => $role_id,
			'name' => $name,
			'email' => $email,
			'comment' => $comment,
			'is_active' => $is_active,
			'authorizations' => $authorizations
		];

		$this->expectException(InvalidArgumentException::class);
		$user = $transformer->deserialize($data);
	}

	public function testSerialize(): void {
		$transformer = new UserTransformer();

		$role_id = 3;	// default id of system defined admin role
		$role_name = 'administrator';
		$is_system_role = TRUE;
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
		$is_active = TRUE;
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
		self::assertTrue(in_array(Permission::LIST_OWN_EQUIPMENT_AUTHORIZATIONS, $data['role']['permissions']));
		self::assertTrue(in_array(Permission::LIST_OWN_CARDS, $data['role']['permissions']));
		self::assertArrayHasKey('authorizations', $data);
		self::assertIsArray($data['authorizations']);
		self::assertTrue(in_array(self::$type->id(), $data['authorizations']));
	}
}