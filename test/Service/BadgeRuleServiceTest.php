<?php

declare(strict_types=1);

namespace Test\Portalbox\Service;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Portalbox\Entity\BadgeRule;
use Portalbox\Entity\Permission;
use Portalbox\Entity\Role;
use Portalbox\Entity\User;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Exception\NotFoundException;
use Portalbox\Model\BadgeRuleModel;
use Portalbox\Service\BadgeRuleService;
use Portalbox\Session\SessionInterface;

final class BadgeRuleServiceTest extends TestCase {
	#region test create()

	public function testCreateThrowsWhenNotAuthenticated() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_UNAUTHENTICATED_CREATE);
		$service->create('not a file path');
	}

	public function testCreateThrowsWhenNotAuthorized() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_UNAUTHORIZED_CREATE);
		$service->create('not a file path');
	}

	public function testCreateThrowsWhenFileIsNotReadable() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_INVALID_BADGE_RULE_DATA);
		// PHP warning is intentionally suppressed in next line for testing
		@$service->create('file_does_not_exist.json');
	}

	public function testCreateThrowsWhenDataIsNotArray() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_INVALID_BADGE_RULE_DATA);
		$service->create(realpath(__DIR__ . '/BadgeRuleServiceTestData/CreateThrowsWhenDataIsNotArray.json'));
	}

	public function testCreateThrowsWhenNameIsNotSpecified() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_NAME_IS_REQUIRED);
		$service->create(realpath(__DIR__ . '/BadgeRuleServiceTestData/CreateThrowsWhenNameIsNotSpecified.json'));
	}

	public function testCreateThrowsWhenNameIsInvalid() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_NAME_IS_INVALID);
		$service->create(realpath(__DIR__ . '/BadgeRuleServiceTestData/CreateThrowsWhenNameIsInvalid.json'));
	}

	public function testCreateSuccess() {
		$name = 'Electronics Technician'; // the sanitized name from the input file

		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::CREATE_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createMock(BadgeRuleModel::class);
		$badgeRuleModel->expects($this->once())->method('create')->with(
			$this->callback(
				fn(BadgeRule $rule) => $rule->name() === $name
			)
		)
		->willReturnArgument(0);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel
		);

		$rule = $service->create(realpath(__DIR__ . '/BadgeRuleServiceTestData/CreateSuccess.json'));

		self::assertInstanceOf(BadgeRule::class, $rule);
		self::assertSame($name, $rule->name());
	}

	#endregion test create()

	#region test read()

	public function testReadThrowsWhenNotAuthenticated() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_UNAUTHENTICATED_READ);
		$service->read(23);
	}

	public function testReadThrowsWhenNotAuthorized() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_UNAUTHORIZED_READ);
		$service->read(23);
	}

	public function testReadThrowsWhenKeyDoesNotExist() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::READ_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel
		);

		self::expectException(NotFoundException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_BADGE_RULE_NOT_FOUND);
		$service->read(23);
	}

	public function testReadSuccess() {
		$rule = new BadgeRule();

		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::READ_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);
		$badgeRuleModel->method('read')->willReturn($rule);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel
		);

		self::assertSame($rule, $service->read(23));
	}

	#endregion test read()

	#region test readAll()

	public function testReadAllThrowsWhenNotAuthenticated() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_UNAUTHENTICATED_READ);
		$service->readAll([]);
	}

	public function testReadAllThrowsWhenNotAuthorized() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_UNAUTHORIZED_READ);
		$service->readAll([]);
	}

	public function testReadAllSuccessNoFilters() {
		$rules = [
			(new BadgeRule())->set_id(1),
			(new BadgeRule())->set_id(2)
		];

		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::LIST_BADGE_RULES])
				)
		);

		$badgeRuleModel = $this->createMock(BadgeRuleModel::class);
		$badgeRuleModel->expects($this->once())->method('search')->willReturn($rules);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel
		);

		self::assertSame($rules, $service->readAll([]));
	}

	#endregion test readAll()

	#region test update()

	public function testUpdateThrowsWhenNotAuthenticated() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_UNAUTHENTICATED_MODIFY);
		$service->update(1, 'not a file path');
	}

	public function testUpdateThrowsWhenNotAuthorized() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_UNAUTHORIZED_MODIFY);
		$service->update(1, 'not a file path');
	}

	public function testUpdateThrowsWhenFileIsNotReadable() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_INVALID_BADGE_RULE_DATA);
		// PHP warning is intentionally suppressed in next line for testing
		@$service->update(1, 'file_does_not_exist.json');
	}

	public function testUpdateThrowsWhenDataIsNotArray() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_INVALID_BADGE_RULE_DATA);
		$service->update(1, realpath(__DIR__ . '/BadgeRuleServiceTestData/UpdateThrowsWhenDataIsNotArray.json'));
	}

	public function testUpdateThrowsWhenNameIsNotSpecified() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_NAME_IS_REQUIRED);
		$service->update(1, realpath(__DIR__ . '/BadgeRuleServiceTestData/UpdateThrowsWhenNameIsNotSpecified.json'));
	}

	public function testUpdateThrowsWhenNameIsInvalid() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel
		);

		self::expectException(InvalidArgumentException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_NAME_IS_INVALID);
		$service->update(1, realpath(__DIR__ . '/BadgeRuleServiceTestData/UpdateThrowsWhenNameIsInvalid.json'));
	}

	public function testUpdateThrowsWhenNotFound() {
		$id = 12;
		$name = 'Electronics Technician'; // the sanitized name from the input file

		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createMock(BadgeRuleModel::class);
		$badgeRuleModel->expects($this->once())->method('update')->with(
			$this->callback(
				fn(BadgeRule $rule) =>
					$rule->id() === $id
					&& $rule->name() === $name
			)
		)
		->willReturn(null);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel
		);

		self::expectException(NotFoundException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_BADGE_RULE_NOT_FOUND);
		$service->update($id, realpath(__DIR__ . '/BadgeRuleServiceTestData/UpdateSuccess.json'));
	}

	public function testUpdateSuccess() {
		$id = 12;
		$name = 'Electronics Technician'; // the sanitized name from the input file

		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::MODIFY_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createMock(BadgeRuleModel::class);
		$badgeRuleModel->expects($this->once())->method('update')->with(
			$this->callback(
				fn(BadgeRule $rule) =>
					$rule->id() === $id
					&& $rule->name() === $name
			)
		)
		->willReturnArgument(0);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel
		);

		$rule = $service->update($id, realpath(__DIR__ . '/BadgeRuleServiceTestData/UpdateSuccess.json'));

		self::assertInstanceOf(BadgeRule::class, $rule);
		self::assertSame($id, $rule->id());
		self::assertSame($name, $rule->name());
	}

	#endregion test update()

	#region test delete()

	public function testDeleteThrowsWhenNotAuthenticated() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(null);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel
		);

		self::expectException(AuthenticationException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_UNAUTHENTICATED_DELETE);
		$service->delete(23);
	}

	public function testDeleteThrowsWhenNotAuthorized() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role((new Role())->set_id(2))
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel
		);

		self::expectException(AuthorizationException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_UNAUTHORIZED_DELETE);
		$service->delete(23);
	}

	public function testDeleteThrowsWhenKeyDoesNotExist() {
		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::DELETE_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel
		);

		self::expectException(NotFoundException::class);
		self::expectExceptionMessage(BadgeRuleService::ERROR_BADGE_RULE_NOT_FOUND);
		$service->delete(23);
	}

	public function testDeleteSuccess() {
		$rule = new BadgeRule();

		$session = $this->createStub(SessionInterface::class);
		$session->method('get_authenticated_user')->willReturn(
			(new User())
				->set_role(
					(new Role())
						->set_id(2)
						->set_permissions([Permission::DELETE_BADGE_RULE])
				)
		);

		$badgeRuleModel = $this->createStub(BadgeRuleModel::class);
		$badgeRuleModel->method('delete')->willReturn($rule);

		$service = new BadgeRuleService(
			$session,
			$badgeRuleModel
		);

		self::assertSame($rule, $service->delete(23));
	}

	#endregion test delete()
}
