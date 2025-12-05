<?php

declare(strict_types=1);

namespace Test\Portalbox;

use Exception;
use InvalidArgumentException;
use PDOException;
use PHPUnit\Framework\TestCase;
use Portalbox\Exception\AuthenticationException;
use Portalbox\Exception\AuthorizationException;
use Portalbox\Exception\NotFoundException;
use Portalbox\Exception\OutOfServiceDeviceException;
use Portalbox\ResponseHandler;

final class ResponseHandlerTest extends TestCase {
	/**
	 * @dataProvider getExceptions
	 */
	public function testSetResponseCode($error, int $responseCode) {
		ResponseHandler::setResponseCode($error);
		self::assertSame($responseCode, http_response_code());
	}

	public static function getExceptions(): iterable {
		yield [new InvalidArgumentException(), 400];

		yield [new AuthenticationException(), 401];

		yield [new AuthorizationException(), 403];

		yield [new NotFoundException(), 404];

		yield [new OutOfServiceDeviceException(), 409];

		yield [new Exception(), 500];

		yield [new PDOException(), 500];
	}
}
