<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Portalbox\Config;
use Portalbox\Entity\Role;
use Portalbox\Entity\User;
use Portalbox\Model\UserModel;
use Portalbox\Query\UserQuery;

final class UserModelTest extends TestCase {
/**
	 * The configuration
	 * @var Config
	 */
	private $config;

	public function setUp(): void {
		parent::setUp();
		$this->config = Config::config();
	}

	public function testModel(): void {
		$model = new UserModel($this->config);

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

		$user_as_created = $model->create($user);

		$user_id = $user_as_created->id();
		self::assertIsInt($user_id);
		self::assertEquals($name, $user_as_created->name());
		self::assertEquals($email, $user_as_created->email());
		self::assertEquals($comment, $user_as_created->comment());
		self::assertEquals($active, $user_as_created->is_active());
		self::assertEquals($role_id, $user_as_created->role()->id());

		$user_as_found = $model->read($user_id);

		self::assertNotNull($user_as_found);
		self::assertEquals($user_id, $user_as_found->id());
		self::assertEquals($name, $user_as_found->name());
		self::assertEquals($email, $user_as_found->email());
		self::assertEquals($comment, $user_as_found->comment());
		self::assertEquals($active, $user_as_found->is_active());
		self::assertEquals($role_id, $user_as_found->role()->id());

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

		$user_as_deleted = $model->delete($user_id);

		self::assertNotNull($user_as_deleted);
		self::assertEquals($user_id, $user_as_deleted->id());
		self::assertEquals($name, $user_as_deleted->name());
		self::assertEquals($email, $user_as_deleted->email());
		self::assertEquals($comment, $user_as_deleted->comment());
		self::assertEquals($active, $user_as_deleted->is_active());
		self::assertEquals($role_id, $user_as_deleted->role()->id());

		$user_as_not_found = $model->read($user_id);

		self::assertNull($user_as_not_found);
	}
}