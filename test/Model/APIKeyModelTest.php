<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Portalbox\Config;
use Portalbox\Entity\APIKey;
use Portalbox\Model\APIKeyModel;

final class APIKeyModelTest extends TestCase {
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
		$model = new APIKeyModel($this->config);

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

		$key_as_deleted = $model->delete($key_id);

		self::assertNotNull($key_as_deleted);
		self::assertEquals($key_id, $key_as_deleted->id());
		self::assertEquals($name, $key_as_deleted->name());
		self::assertEquals($token, $key_as_deleted->token());

		$key_as_not_found = $model->read($key_id);

		self::assertNull($key_as_not_found);
	}
}