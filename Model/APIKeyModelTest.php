<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Portalbox\Config;
use Portalbox\Entity\APIKey;
use Portalbox\Model\APIKeyModel;
use Portalbox\Query\APIKeyQuery;

final class APIKeyModelTest extends TestCase {
	public function testModel(): void {
		$model = new APIKeyModel(Config::config());

		$name = 'Google App Suite Integration';
		$token = 'ABCDEF01234567890123456789ABCDEF';

		$key = (new APIKey())
			->set_name($name)
			->set_token($token);

		$key_as_created = $model->create($key);

		$key_id = $key_as_created->id();
		self::assertIsInt($key_id);
		self::assertEquals($name, $key_as_created->name());
		self::assertEquals($token, $key_as_created->token());

		$key_as_found = $model->read($key_id);

		self::assertNotNull($key_as_found);
		self::assertEquals($key_id, $key_as_found->id());
		self::assertEquals($name, $key_as_found->name());
		self::assertEquals($token, $key_as_found->token());

		$name = 'Wordpress Integration';
		$key_as_found->set_name($name);

		$key_as_modified = $model->update($key_as_found);

		self::assertNotNull($key_as_modified);
		self::assertEquals($key_id, $key_as_modified->id());
		self::assertEquals($name, $key_as_modified->name());
		self::assertEquals($token, $key_as_modified->token());

		$query = (new APIKeyQuery)->set_token($token);
		$keys_as_found = $model->search($query);
		self::assertNotNull($keys_as_found);
		self::assertIsIterable($keys_as_found);
		self::assertCount(1, $keys_as_found);
		self::assertEquals($key_id, $keys_as_found[0]->id());
		self::assertEquals($name, $keys_as_found[0]->name());
		self::assertEquals($token, $keys_as_found[0]->token());

		$key_as_deleted = $model->delete($key_id);

		self::assertNotNull($key_as_deleted);
		self::assertEquals($key_id, $key_as_deleted->id());
		self::assertEquals($name, $key_as_deleted->name());
		self::assertEquals($token, $key_as_deleted->token());

		$key_as_not_found = $model->read($key_id);

		self::assertNull($key_as_not_found);
	}
}