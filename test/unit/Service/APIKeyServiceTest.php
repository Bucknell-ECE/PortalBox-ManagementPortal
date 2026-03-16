<?php

declare(strict_types=1);

namespace Test\Portalbox\Service;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Portalbox\Enumeration\Permission;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Exception\NotFoundException;
use Portalbox\Model\APIKeyModel;
use Portalbox\Query\APIKeyQuery;
use Portalbox\Service\APIKeyService;
use Portalbox\Session;
use Portalbox\Type\APIKey;
use Portalbox\Type\Role;
use Portalbox\Type\User;

final class APIKeyServiceTest extends TestCase {
	#region test create()

	public function testCreateThrowsWhenNotAuthenticated() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$apiKeyModel = $this->createStub(APIKeyModel::class);

		$service = new APIKeyService(
			$session,
			$apiKeyModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(APIKeyService::ERROR_UNAUTHENTICATED_CREATE);
		$service->create('not a file path');
	}

	public function testCreateThrowsWhenNotAuthorized() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$apiKeyModel = $this->createStub(APIKeyModel::class);

		$service = new APIKeyService(
			$session,
			$apiKeyModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(APIKeyService::ERROR_UNAUTHORIZED_CREATE);
		$service->create('not a file path');
	}

	public function testCreateThrowsWhenFileIsNotReadable() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_API_KEY])
				)
		);

		$apiKeyModel = $this->createStub(APIKeyModel::class);

		$service = new APIKeyService(
			$session,
			$apiKeyModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(APIKeyService::ERROR_INVALID_API_KEY_DATA);
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
						->set_permissions([Permission::CREATE_API_KEY])
				)
		);

		$apiKeyModel = $this->createStub(APIKeyModel::class);

		$service = new APIKeyService(
			$session,
			$apiKeyModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(APIKeyService::ERROR_INVALID_API_KEY_DATA);
		$service->create(realpath(__DIR__ . '/APIKeyServiceTestData/CreateThrowsWhenDataIsNotArray.json'));
	}

	public function testCreateThrowsWhenNameIsNotSpecified() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_API_KEY])
				)
		);

		$apiKeyModel = $this->createStub(APIKeyModel::class);

		$service = new APIKeyService(
			$session,
			$apiKeyModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(APIKeyService::ERROR_NAME_IS_REQUIRED);
		$service->create(realpath(__DIR__ . '/APIKeyServiceTestData/CreateThrowsWhenNameIsNotSpecified.json'));
	}

	public function testCreateThrowsWhenNameIsInvalid() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_API_KEY])
				)
		);

		$apiKeyModel = $this->createStub(APIKeyModel::class);

		$service = new APIKeyService(
			$session,
			$apiKeyModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(APIKeyService::ERROR_NAME_IS_INVALID);
		$service->create(realpath(__DIR__ . '/APIKeyServiceTestData/CreateThrowsWhenNameIsInvalid.json'));
	}

	public function testCreateSuccess() {
		$name = 'Portalbox Auth Token'; // the sanitized name from the input file
		$token = '1234567890'; // the token in the file which we ignore

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_API_KEY])
				)
		);

		$apiKeyModel = $this->createMock(APIKeyModel::class);
		$apiKeyModel->expects($this->once())->method('create')->with(
			$this->callback(
				fn(APIKey $key) =>
					$key->name() === $name
					&& $key->token() !== $token
			)
		)
		->willReturnArgument(0);

		$service = new APIKeyService(
			$session,
			$apiKeyModel
		);

		$key = $service->create(realpath(__DIR__ . '/APIKeyServiceTestData/CreateSuccess.json'));

		self::assertInstanceOf(APIKey::class, $key);
		self::assertSame($name, $key->name());
		// not testing for token here because it is randomly generated by the Type.
		// @todo move random token generation into service
	}

	#endregion test create()

	#region test read()

	public function testReadThrowsWhenNotAuthenticated() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$apiKeyModel = $this->createStub(APIKeyModel::class);

		$service = new APIKeyService(
			$session,
			$apiKeyModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(APIKeyService::ERROR_UNAUTHENTICATED_READ);
		$service->read(123456789);
	}

	public function testReadThrowsWhenNotAuthorized() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$apiKeyModel = $this->createStub(APIKeyModel::class);

		$service = new APIKeyService(
			$session,
			$apiKeyModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(APIKeyService::ERROR_UNAUTHORIZED_READ);
		$service->read(123456789);
	}

	public function testReadThrowsWhenKeyDoesNotExist() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::READ_API_KEY])
				)
		);

		$apiKeyModel = $this->createStub(APIKeyModel::class);

		$service = new APIKeyService(
			$session,
			$apiKeyModel
		);

		self::expectException(NotFoundException::class);
		self::expectExceptionMessage(APIKeyService::ERROR_API_KEY_NOT_FOUND);
		$service->read(123456789);
	}

	public function testReadSuccess() {
		$key = new APIKey();

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::READ_API_KEY])
				)
		);

		$apiKeyModel = $this->createStub(APIKeyModel::class);
		$apiKeyModel->method('read')->willReturn($key);

		$service = new APIKeyService(
			$session,
			$apiKeyModel
		);

		self::assertSame($key, $service->read(123456789));
	}

	#endregion test read()

	#region test readAll()

	public function testReadAllThrowsWhenNotAuthenticated() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$apiKeyModel = $this->createStub(APIKeyModel::class);

		$service = new APIKeyService(
			$session,
			$apiKeyModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(APIKeyService::ERROR_UNAUTHENTICATED_READ);
		$service->readAll([]);
	}

	public function testReadAllThrowsWhenNotAuthorized() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$apiKeyModel = $this->createStub(APIKeyModel::class);

		$service = new APIKeyService(
			$session,
			$apiKeyModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(APIKeyService::ERROR_UNAUTHORIZED_READ);
		$service->readAll([]);
	}

	public function testReadAllSuccessNoFilters() {
		$keys = [
			(new APIKey())->set_id(1),
			(new APIKey())->set_id(2)
		];

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::LIST_API_KEYS])
				)
		);

		$apiKeyModel = $this->createMock(APIKeyModel::class);
		$apiKeyModel->expects($this->once())->method('search')->with(
			$this->callback(
				fn(APIKeyQuery $query) => $query->token() === null
			)
		)->willReturn($keys);

		$service = new APIKeyService(
			$session,
			$apiKeyModel
		);

		self::assertSame($keys, $service->readAll([]));
	}

	public function testReadAllSuccessFilterForToken() {
		$token = '1234567890';

		$keys = [
			(new APIKey())->set_id(1)->set_token($token)
		];

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::LIST_API_KEYS])
				)
		);

		$apiKeyModel = $this->createMock(APIKeyModel::class);
		$apiKeyModel->expects($this->once())->method('search')->with(
			$this->callback(
				fn(APIKeyQuery $query) => $query->token() === $token
			)
		)->willReturn($keys);

		$service = new APIKeyService(
			$session,
			$apiKeyModel
		);

		self::assertSame($keys, $service->readAll(['token' => $token]));
	}

	#endregion test readAll()

	#region test update()

	public function testUpdateThrowsWhenNotAuthenticated() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$apiKeyModel = $this->createStub(APIKeyModel::class);

		$service = new APIKeyService(
			$session,
			$apiKeyModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(APIKeyService::ERROR_UNAUTHENTICATED_MODIFY);
		$service->update(1, 'not a file path');
	}

	public function testUpdateThrowsWhenNotAuthorized() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$apiKeyModel = $this->createStub(APIKeyModel::class);

		$service = new APIKeyService(
			$session,
			$apiKeyModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(APIKeyService::ERROR_UNAUTHORIZED_MODIFY);
		$service->update(1, 'not a file path');
	}

	public function testUpdateThrowsWhenFileIsNotReadable() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_API_KEY])
				)
		);

		$apiKeyModel = $this->createStub(APIKeyModel::class);

		$service = new APIKeyService(
			$session,
			$apiKeyModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(APIKeyService::ERROR_INVALID_API_KEY_DATA);
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
						->set_permissions([Permission::MODIFY_API_KEY])
				)
		);

		$apiKeyModel = $this->createStub(APIKeyModel::class);

		$service = new APIKeyService(
			$session,
			$apiKeyModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(APIKeyService::ERROR_INVALID_API_KEY_DATA);
		$service->update(1, realpath(__DIR__ . '/APIKeyServiceTestData/UpdateThrowsWhenDataIsNotArray.json'));
	}

	public function testUpdateThrowsWhenNameIsNotSpecified() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_API_KEY])
				)
		);

		$apiKeyModel = $this->createStub(APIKeyModel::class);

		$service = new APIKeyService(
			$session,
			$apiKeyModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(APIKeyService::ERROR_NAME_IS_REQUIRED);
		$service->update(1, realpath(__DIR__ . '/APIKeyServiceTestData/UpdateThrowsWhenNameIsNotSpecified.json'));
	}

	public function testUpdateThrowsWhenNameIsInvalid() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_API_KEY])
				)
		);

		$apiKeyModel = $this->createStub(APIKeyModel::class);

		$service = new APIKeyService(
			$session,
			$apiKeyModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(APIKeyService::ERROR_NAME_IS_INVALID);
		$service->update(1, realpath(__DIR__ . '/APIKeyServiceTestData/UpdateThrowsWhenNameIsInvalid.json'));
	}

	public function testUpdateThrowsWhenNotFound() {
		$id = 12;
		$name = 'Portalbox Auth Token'; // the sanitized name from the input file
		$token = '1234567890'; // the token in the file which we ignore

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_API_KEY])
				)
		);

		$apiKeyModel = $this->createMock(APIKeyModel::class);
		$apiKeyModel->expects($this->once())->method('update')->with(
			$this->callback(
				fn(APIKey $key) =>
					$key->id() === $id
					&& $key->name() === $name
					&& $key->token() !== $token
			)
		)
		->willReturn(null);

		$service = new APIKeyService(
			$session,
			$apiKeyModel
		);

		self::expectException(NotFoundException::class);
		self::expectExceptionMessage(APIKeyService::ERROR_API_KEY_NOT_FOUND);
		$service->update($id, realpath(__DIR__ . '/APIKeyServiceTestData/UpdateSuccess.json'));
	}

	public function testUpdateSuccess() {
		$id = 12;
		$name = 'Portalbox Auth Token'; // the sanitized name from the input file
		$token = '1234567890'; // the token in the file which we ignore
		$validToken = 'abcdef1234567890';

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_API_KEY])
				)
		);

		$apiKeyModel = $this->createMock(APIKeyModel::class);
		$apiKeyModel->expects($this->once())->method('update')->with(
			$this->callback(
				fn(APIKey $key) =>
					$key->id() === $id
					&& $key->name() === $name
					&& $key->token() !== $token
			)
		)
		->willReturn(
			(new APIKey())
				->set_id($id)
				->set_name($name)
				->set_token($validToken) // model fills this in
		);

		$service = new APIKeyService(
			$session,
			$apiKeyModel
		);

		$key = $service->update($id, realpath(__DIR__ . '/APIKeyServiceTestData/UpdateSuccess.json'));

		self::assertInstanceOf(APIKey::class, $key);
		self::assertSame($id, $key->id());
		self::assertSame($name, $key->name());
		self::assertSame($validToken, $key->token());
	}

	#endregion test update()

	#region test delete()

	public function testDeleteThrowsWhenNotAuthenticated() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$apiKeyModel = $this->createStub(APIKeyModel::class);

		$service = new APIKeyService(
			$session,
			$apiKeyModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(APIKeyService::ERROR_UNAUTHENTICATED_DELETE);
		$service->delete(123456789);
	}

	public function testDeleteThrowsWhenNotAuthorized() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$apiKeyModel = $this->createStub(APIKeyModel::class);

		$service = new APIKeyService(
			$session,
			$apiKeyModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(APIKeyService::ERROR_UNAUTHORIZED_DELETE);
		$service->delete(123456789);
	}

	public function testDeleteThrowsWhenKeyDoesNotExist() {
		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::DELETE_API_KEY])
				)
		);

		$apiKeyModel = $this->createStub(APIKeyModel::class);

		$service = new APIKeyService(
			$session,
			$apiKeyModel
		);

		self::expectException(NotFoundException::class);
		self::expectExceptionMessage(APIKeyService::ERROR_API_KEY_NOT_FOUND);
		$service->delete(123456789);
	}

	public function testDeleteSuccess() {
		$key = new APIKey();

		$session = $this->createStub(Session::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::DELETE_API_KEY])
				)
		);

		$apiKeyModel = $this->createStub(APIKeyModel::class);
		$apiKeyModel->method('delete')->willReturn($key);

		$service = new APIKeyService(
			$session,
			$apiKeyModel
		);

		self::assertSame($key, $service->delete(123456789));
	}

	#endregion test delete()
}
