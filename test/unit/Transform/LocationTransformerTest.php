<?php

declare(strict_types=1);

namespace Test\Portalbox\Transform;

use PHPUnit\Framework\TestCase;
use Portalbox\Transform\LocationTransformer;
use Portalbox\Type\Location;

final class LocationTransformerTest extends TestCase {
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
