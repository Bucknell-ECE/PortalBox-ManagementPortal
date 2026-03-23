<?php

declare(strict_types=1);

namespace Test\Portalbox\Model;

use PHPUnit\Framework\TestCase;
use Portalbox\Config;
use Portalbox\Enumeration\Permission;
use Portalbox\Model\APIKeyModel;
use Portalbox\Query\APIKeyQuery;
use Portalbox\Type\APIKey;

final class APIKeyModelTest extends TestCase {
	public function testCreateReadUpdateDelete(): void {
		$model = new APIKeyModel(Config::config());

		$name = 'Google App Suite Integration';
		$token = 'ABCDEF01234567890123456789ABCDEF';

		$permissions = [
			Permission::READ_USER,
			Permission::LIST_PAYMENTS
		];

		$key = $model->create(
			(new APIKey())
				->set_name($name)
				->set_token($token)
				->set_permissions($permissions)
		);

		$id = $key->id();
		self::assertIsInt($id);
		self::assertSame($name, $key->name());
		self::assertSame($token, $key->token());
		self::assertEqualsCanonicalizing($permissions, $key->permissions());

		$key = $model->read($id);

		self::assertInstanceOf(APIKey::class, $key);
		self::assertSame($id, $key->id());
		self::assertSame($name, $key->name());
		self::assertSame($token, $key->token());
		self::assertEqualsCanonicalizing($permissions, $key->permissions());

		$name = 'Wordpress Integration';
		$permissions = [
			Permission::READ_USER,
			Permission::LIST_CHARGES
		];

		$key = $model->update(
			$key
				->set_name($name)
				->set_token('11556654433221554433255443321155')
				->set_permissions($permissions)
		);

		self::assertInstanceOf(APIKey::class, $key);
		self::assertSame($id, $key->id());
		self::assertSame($name, $key->name());
		self::assertSame($token, $key->token());
		self::assertEqualsCanonicalizing($permissions, $key->permissions());

		$key = $model->delete($id);

		self::assertInstanceOf(APIKey::class, $key);
		self::assertSame($id, $key->id());
		self::assertSame($name, $key->name());
		self::assertSame($token, $key->token());
		self::assertEqualsCanonicalizing($permissions, $key->permissions());

		self::assertNull($model->read($id));
	}

	public function testSearch(): void {
		$model = new APIKeyModel(Config::config());

		$name1 = 'Google App Suite Integration';
		$name2 = 'Wordpress Integration';
		$token1 = 'ABCDEF01234567890123456789ABCDEF';
		$token2 = '11556654433221554433255443321155';

		$keyId1 = $model->create(
			(new APIKey())
				->set_name($name1)
				->set_token($token1)
		)->id();

		$keyId2 = $model->create(
			(new APIKey())
				->set_name($name2)
				->set_token($token2)
		)->id();

		$query = new APIKeyQuery();
		$keyIds = array_map(
			fn (APIKey $key) => $key->id(),
			$model->search($query)
		);
		self::assertContains($keyId1, $keyIds);
		self::assertContains($keyId2, $keyIds);

		$query = (new APIKeyQuery())->set_token($token2);
		$keyIds = array_map(
			fn (APIKey $key) => $key->id(),
			$model->search($query)
		);
		self::assertNotContains($keyId1, $keyIds);
		self::assertContains($keyId2, $keyIds);

		// cleanup
		$model->delete($keyId1);
		$model->delete($keyId2);
	}
}
