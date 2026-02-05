<?php

declare(strict_types=1);

namespace Test\Portalbox\Service;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Portalbox\Enumeration\Permission;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Exception\NotFoundException;
use Portalbox\Model\LocationModel;
use Portalbox\Service\LocationService;
use Portalbox\Session;
use Portalbox\Type\Location;
use Portalbox\Type\Role;
use Portalbox\Type\User;

final class LocationServiceTest extends TestCase {
	#region test create()

	public function testCreateThrowsWhenNotAuthenticated() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$locationModel = $this->createStub(LocationModel::class);

		$service = new LocationService(
			$session,
			$locationModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(LocationService::ERROR_UNAUTHENTICATED_CREATE);
		$service->create('not a file path');
	}

	public function testCreateThrowsWhenNotAuthorized() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$locationModel = $this->createStub(LocationModel::class);

		$service = new LocationService(
			$session,
			$locationModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(LocationService::ERROR_UNAUTHORIZED_CREATE);
		$service->create('not a file path');
	}

	public function testCreateThrowsWhenFileIsNotReadable() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_LOCATION])
				)
		);

		$locationModel = $this->createStub(LocationModel::class);

		$service = new LocationService(
			$session,
			$locationModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(LocationService::ERROR_INVALID_LOCATION_DATA);
		// PHP warning is intentionally suppressed in next line for testing
		@$service->create('file_does_not_exist.json');
	}

	public function testCreateThrowsWhenDataIsNotArray() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_LOCATION])
				)
		);

		$locationModel = $this->createStub(LocationModel::class);

		$service = new LocationService(
			$session,
			$locationModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(LocationService::ERROR_INVALID_LOCATION_DATA);
		$service->create(realpath(__DIR__ . '/LocationServiceTestData/CreateThrowsWhenDataIsNotArray.json'));
	}

	public function testCreateThrowsWhenNameIsNotSpecified() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_LOCATION])
				)
		);

		$locationModel = $this->createStub(LocationModel::class);

		$service = new LocationService(
			$session,
			$locationModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(LocationService::ERROR_NAME_IS_REQUIRED);
		$service->create(realpath(__DIR__ . '/LocationServiceTestData/CreateThrowsWhenNameIsNotSpecified.json'));
	}

	public function testCreateThrowsWhenNameIsInvalid() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_LOCATION])
				)
		);

		$locationModel = $this->createStub(LocationModel::class);

		$service = new LocationService(
			$session,
			$locationModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(LocationService::ERROR_NAME_IS_INVALID);
		$service->create(realpath(__DIR__ . '/LocationServiceTestData/CreateThrowsWhenNameIsInvalid.json'));
	}

	public function testCreateSuccess() {
		$name = 'The Laboratory'; // the sanitized name from the input file

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_LOCATION])
				)
		);

		$locationModel = $this->createMock(LocationModel::class);
		$locationModel->expects($this->once())->method('create')->with(
			$this->callback(
				fn(Location $location) =>
					$location->name() === $name
			)
		)
		->willReturnArgument(0);

		$service = new LocationService(
			$session,
			$locationModel
		);

		$location = $service->create(realpath(__DIR__ . '/LocationServiceTestData/CreateSuccess.json'));

		self::assertInstanceOf(Location::class, $location);
		self::assertSame($name, $location->name());
	}

	#endregion test create()

	#region test read()

	public function testReadThrowsWhenNotAuthenticated() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$locationModel = $this->createStub(LocationModel::class);

		$service = new LocationService(
			$session,
			$locationModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(LocationService::ERROR_UNAUTHENTICATED_READ);
		$service->read(19);
	}

	public function testReadThrowsWhenNotAuthorized() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$locationModel = $this->createStub(LocationModel::class);

		$service = new LocationService(
			$session,
			$locationModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(LocationService::ERROR_UNAUTHORIZED_READ);
		$service->read(19);
	}

	public function testReadThrowsWhenLocationDoesNotExist() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::READ_LOCATION])
				)
		);

		$locationModel = $this->createStub(LocationModel::class);

		$service = new LocationService(
			$session,
			$locationModel
		);

		self::expectException(NotFoundException::class);
		self::expectExceptionMessage(LocationService::ERROR_LOCATION_NOT_FOUND);
		$service->read(19);
	}

	public function testReadSuccess() {
		$location = new Location();

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::READ_LOCATION])
				)
		);

		$locationModel = $this->createStub(LocationModel::class);
		$locationModel->method('read')->willReturn($location);

		$service = new LocationService(
			$session,
			$locationModel
		);

		self::assertSame($location, $service->read(19));
	}

	#endregion test read()

	#region test readAll()

	public function testReadAllThrowsWhenNotAuthenticated() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$locationModel = $this->createStub(LocationModel::class);

		$service = new LocationService(
			$session,
			$locationModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(LocationService::ERROR_UNAUTHENTICATED_READ);
		$service->readAll();
	}

	public function testReadAllThrowsWhenNotAuthorized() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$locationModel = $this->createStub(LocationModel::class);

		$service = new LocationService(
			$session,
			$locationModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(LocationService::ERROR_UNAUTHORIZED_READ);
		$service->readAll();
	}

	public function testReadAllSuccess() {
		$locations = [
			(new Location())->set_id(1),
			(new Location())->set_id(2)
		];

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::LIST_LOCATIONS])
				)
		);

		$locationModel = $this->createMock(LocationModel::class);
		$locationModel->expects($this->once())->method('search')->willReturn($locations);

		$service = new LocationService(
			$session,
			$locationModel
		);

		self::assertSame($locations, $service->readAll());
	}

	#endregion test readAll()

	#region test update()

	public function testUpdateThrowsWhenNotAuthenticated() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$locationModel = $this->createStub(LocationModel::class);

		$service = new LocationService(
			$session,
			$locationModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(LocationService::ERROR_UNAUTHENTICATED_MODIFY);
		$service->update(1, 'not a file path');
	}

	public function testUpdateThrowsWhenNotAuthorized() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$locationModel = $this->createStub(LocationModel::class);

		$service = new LocationService(
			$session,
			$locationModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(LocationService::ERROR_UNAUTHORIZED_MODIFY);
		$service->update(1, 'not a file path');
	}

	public function testUpdateThrowsWhenFileIsNotReadable() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_LOCATION])
				)
		);

		$locationModel = $this->createStub(LocationModel::class);

		$service = new LocationService(
			$session,
			$locationModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(LocationService::ERROR_INVALID_LOCATION_DATA);
		// PHP warning is intentionally suppressed in next line for testing
		@$service->update(1, 'file_does_not_exist.json');
	}

	public function testUpdateThrowsWhenDataIsNotArray() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_LOCATION])
				)
		);

		$locationModel = $this->createStub(LocationModel::class);

		$service = new LocationService(
			$session,
			$locationModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(LocationService::ERROR_INVALID_LOCATION_DATA);
		$service->update(1, realpath(__DIR__ . '/LocationServiceTestData/UpdateThrowsWhenDataIsNotArray.json'));
	}

	public function testUpdateThrowsWhenNameIsNotSpecified() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_LOCATION])
				)
		);

		$locationModel = $this->createStub(LocationModel::class);

		$service = new LocationService(
			$session,
			$locationModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(LocationService::ERROR_NAME_IS_REQUIRED);
		$service->update(1, realpath(__DIR__ . '/LocationServiceTestData/UpdateThrowsWhenNameIsNotSpecified.json'));
	}

	public function testUpdateThrowsWhenNameIsInvalid() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_LOCATION])
				)
		);

		$locationModel = $this->createStub(LocationModel::class);

		$service = new LocationService(
			$session,
			$locationModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(LocationService::ERROR_NAME_IS_INVALID);
		$service->update(1, realpath(__DIR__ . '/LocationServiceTestData/UpdateThrowsWhenNameIsInvalid.json'));
	}

	public function testUpdateThrowsWhenNotFound() {
		$id = 12;
		$name = 'The Laboratory'; // the sanitized name from the input file

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_LOCATION])
				)
		);

		$locationModel = $this->createMock(LocationModel::class);
		$locationModel->expects($this->once())->method('update')->with(
			$this->callback(
				fn(Location $location) =>
					$location->id() === $id
					&& $location->name() === $name
			)
		)
		->willReturn(null);

		$service = new LocationService(
			$session,
			$locationModel
		);

		self::expectException(NotFoundException::class);
		self::expectExceptionMessage(LocationService::ERROR_LOCATION_NOT_FOUND);
		$service->update($id, realpath(__DIR__ . '/LocationServiceTestData/UpdateSuccess.json'));
	}

	public function testUpdateSuccess() {
		$id = 12;
		$name = 'The Laboratory'; // the sanitized name from the input file

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_LOCATION])
				)
		);

		$locationModel = $this->createMock(LocationModel::class);
		$locationModel->expects($this->once())->method('update')->with(
			$this->callback(
				fn(Location $location) =>
					$location->id() === $id
					&& $location->name() === $name
			)
		)
		->willReturnArgument(0);

		$service = new LocationService(
			$session,
			$locationModel
		);

		$location = $service->update($id, realpath(__DIR__ . '/LocationServiceTestData/UpdateSuccess.json'));

		self::assertInstanceOf(Location::class, $location);
		self::assertSame($id, $location->id());
		self::assertSame($name, $location->name());
	}

	#endregion test update()
}
