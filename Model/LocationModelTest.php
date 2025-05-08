<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

use Portalbox\Config;
use Portalbox\Entity\Location;
use Portalbox\Model\LocationModel;

final class LocationModelTest extends TestCase {
	public function testModel(): void {
		$model = new LocationModel(Config::config());

		$name = 'Robotics Shop';

		$location = (new Location())
			->set_name($name);

		$location_as_created = $model->create($location);

		$location_id = $location_as_created->id();
		self::assertIsInt($location_id);
		self::assertEquals($name, $location_as_created->name());

		$location_as_found = $model->read($location_id);

		self::assertNotNull($location_as_found);
		self::assertEquals($location_id, $location_as_found->id());
		self::assertEquals($name, $location_as_found->name());

		$name = 'Cybernetics Shop';
		$location_as_found->set_name($name);

		$location_as_modified = $model->update($location_as_found);

		self::assertNotNull($location_as_modified);
		self::assertEquals($location_id, $location_as_modified->id());
		self::assertEquals($name, $location_as_modified->name());

		$locations_as_found = $model->search();
		self::assertIsArray($locations_as_found);
		self::assertNotEmpty($locations_as_found);
		self::assertContainsOnlyInstancesOf(Location::class, $locations_as_found);

		$location_as_deleted = $model->delete($location_id);

		self::assertNotNull($location_as_deleted);
		self::assertEquals($location_id, $location_as_deleted->id());
		self::assertEquals($name, $location_as_deleted->name());

		$location_as_not_found = $model->read($location_id);

		self::assertNull($location_as_not_found);
	}
}