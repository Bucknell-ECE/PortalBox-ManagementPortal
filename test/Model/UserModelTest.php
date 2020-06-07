<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Portalbox\Config;

use Portalbox\Entity\ChargePolicy;
use Portalbox\Entity\EquipmentType;
use Portalbox\Entity\Role;
use Portalbox\Entity\User;

use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Model\UserModel;

use Portalbox\Query\UserQuery;

final class UserModelTest extends TestCase {
	/**
	 * An equipment type which exists in the db
	 */
	private static $type;

	/**
	 * The configuration
	 * @var Config
	 */
	private static $config;

	public static function setUpBeforeClass(): void {
		parent::setUp();
		self::$config = Config::config();

		// provision an equipment type in the db
		$model = new EquipmentTypeModel(self::$config);

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
		$model = new EquipmentTypeModel(self::$config);
		$model->delete(self::$type->id());
	}

	public function testModel(): void {
		$model = new UserModel(self::$config);

		$role_id = 3;	// default id of system defined admin role

		$role = (new Role())
			->set_id($role_id);

		$name = 'Tom Egan';
		$email = 'tom@ficticious.tld';
		$comment = 'Test Monkey';
		$active = TRUE;
		$authorizations = [self::$type->id()];
		$num_authorizations = count($authorizations);

		$user = (new User())
			->set_name($name)
			->set_email($email)
			->set_comment($comment)
			->set_is_active($active)
			->set_role($role)
			->set_authorizations($authorizations);

		$user_as_created = $model->create($user);

		$user_id = $user_as_created->id();
		self::assertIsInt($user_id);
		self::assertEquals($name, $user_as_created->name());
		self::assertEquals($email, $user_as_created->email());
		self::assertEquals($comment, $user_as_created->comment());
		self::assertEquals($active, $user_as_created->is_active());
		self::assertEquals($role_id, $user_as_created->role()->id());
		self::assertIsIterable($user->authorizations());
		self::assertCount($num_authorizations, $user->authorizations());
		self::assertContains(self::$type->id(), $user->authorizations());

		$user_as_found = $model->read($user_id);

		self::assertNotNull($user_as_found);
		self::assertEquals($user_id, $user_as_found->id());
		self::assertEquals($name, $user_as_found->name());
		self::assertEquals($email, $user_as_found->email());
		self::assertEquals($comment, $user_as_found->comment());
		self::assertEquals($active, $user_as_found->is_active());
		self::assertEquals($role_id, $user_as_found->role()->id());
		self::assertIsIterable($user->authorizations());
		self::assertCount($num_authorizations, $user->authorizations());
		self::assertContains(self::$type->id(), $user->authorizations());

		$name = 'Matt Lamparter';
		$email = 'matt@ficticious.tld';
		$comment = 'Test Hominid';
		$active = FALSE;

		$user_as_found
			->set_name($name)
			->set_email($email)
			->set_comment($comment)
			->set_is_active($active);

		$user_as_modified = $model->update($user_as_found);

		self::assertNotNull($user_as_modified);
		self::assertEquals($user_id, $user_as_modified->id());
		self::assertEquals($name, $user_as_modified->name());
		self::assertEquals($email, $user_as_modified->email());
		self::assertEquals($comment, $user_as_modified->comment());
		self::assertEquals($active, $user_as_modified->is_active());
		self::assertEquals($role_id, $user_as_modified->role()->id());

		$query = (new UserQuery)->set_email($email);
		$users_as_found = $model->search($query);
		self::assertNotNull($users_as_found);
		self::assertIsIterable($users_as_found);
		self::assertCount(1, $users_as_found);
		self::assertEquals($user_id, $users_as_found[0]->id());
		self::assertEquals($name, $users_as_found[0]->name());
		self::assertEquals($comment, $users_as_found[0]->comment());
		self::assertEquals($active, $users_as_found[0]->is_active());
		self::assertEquals($role_id, $users_as_found[0]->role()->id());
		self::assertIsIterable($user->authorizations());
		self::assertCount($num_authorizations, $user->authorizations());
		self::assertContains(self::$type->id(), $user->authorizations());

		$user_as_deleted = $model->delete($user_id);

		self::assertNotNull($user_as_deleted);
		self::assertEquals($user_id, $user_as_deleted->id());
		self::assertEquals($name, $user_as_deleted->name());
		self::assertEquals($email, $user_as_deleted->email());
		self::assertEquals($comment, $user_as_deleted->comment());
		self::assertEquals($active, $user_as_deleted->is_active());
		self::assertEquals($role_id, $user_as_deleted->role()->id());
		self::assertIsIterable($user->authorizations());
		self::assertCount($num_authorizations, $user->authorizations());
		self::assertContains(self::$type->id(), $user->authorizations());

		$user_as_not_found = $model->read($user_id);

		self::assertNull($user_as_not_found);
	}
}