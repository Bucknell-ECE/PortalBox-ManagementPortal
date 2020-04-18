<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Portalbox\Entity\Location;

final class LocationTest extends TestCase {
	public function testAgreement(): void {
		$id = 42;
		$name = 'South Side Pottery Studio';

		$location = (new Location())
			->set_id($id)
			->set_name($name);

		self::assertEquals($id, $location->id());
		self::assertEquals($name, $location->name());
	}
}