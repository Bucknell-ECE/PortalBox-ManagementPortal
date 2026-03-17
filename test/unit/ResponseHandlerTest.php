<?php

declare(strict_types=1);

namespace Test\Portalbox;

use Exception;
use InvalidArgumentException;
use PDOException;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use PHPUnit\Framework\TestCase;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Exception\NotFoundException;
use Portalbox\Exception\OutOfServiceDeviceException;
use Portalbox\ResponseHandler;

/**
 * Note we can't use a data provider and run the tests in separate processes as
 * needed to test setting the HTTP response code
 */
#[RunTestsInSeparateProcesses]
final class ResponseHandlerTest extends TestCase {
	public function testSetResponseCodeWithInvalidArgumentException() {
		ResponseHandler::setResponseCode(new InvalidArgumentException());
		self::assertSame(400, http_response_code());
	}

	public function testSetResponseCodeWithAuthenticationException() {
		ResponseHandler::setResponseCode(new AuthenticationException());
		self::assertSame(401, http_response_code());
	}

	public function testSetResponseCodeWithAuthorizationException() {
		ResponseHandler::setResponseCode(new AuthorizationException());
		self::assertSame(403, http_response_code());
	}

	public function testSetResponseCodeWithNotFoundException() {
		ResponseHandler::setResponseCode(new NotFoundException());
		self::assertSame(404, http_response_code());
	}

	public function testSetResponseCodeWithOutOfServiceDeviceException() {
		ResponseHandler::setResponseCode(new OutOfServiceDeviceException());
		self::assertSame(409, http_response_code());
	}

	public function testSetResponseCodeWithException() {
		ResponseHandler::setResponseCode(new Exception());
		self::assertSame(500, http_response_code());
	}

	public function testSetResponseCodeWithPDOException() {
		ResponseHandler::setResponseCode(new PDOException());
		self::assertSame(500, http_response_code());
	}
}
