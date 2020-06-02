<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use PortalBox\Entity\Location;

use Portalbox\Transform\LocationTransformer;

final class LocationTransformerTest extends TestCase {
	public function testDeserialize(): void {
		$transformer = new LocationTransformer();

		$id = 42;
		$name = 'The Maker\'s Den';

		$data = [
			'id' => $id,
			'name' => $name
		];

		$location = $transformer->deserialize($data);

		self::assertNotNull($location);
		self::assertNull($location->id());
		self::assertEquals($name, $location->name());
	}

	public function testDeserializeInvalidDataUserID(): void {
		$transformer = new LocationTransformer();

		$id = 42;

		$data = [
			'id' => $id
		];

		$this->expectException(InvalidArgumentException::class);
		$location = $transformer->deserialize($data);
	}

	public function testSerialize(): void {
		$transformer = new LocationTransformer();

		$id = 42;
		$name = 'The Maker\'s Den';

		$location = (new Location())
			->set_id($id)
			->set_name($name);

		$data = $transformer->serialize($location, true);

		self::assertNotNull($data);
		self::assertArrayHasKey('id', $data);
		self::assertEquals($id, $data['id']);
		self::assertArrayHasKey('name', $data);
		self::assertEquals($name, $data['name']);
	}
}