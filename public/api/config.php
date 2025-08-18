<?php
// configuration needs to be accessible before the front end can
// authenticate as this is how we inject OAUTH API Keys etc. We want to
// restrict access to javascript that was served from this server though

require '../../src/autoload.php';

use Portalbox\Config;
use Portalbox\ResponseHandler;

use Portalbox\Transform\ConfigOutputTransformer;

try {
	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET':
			$transformer = new ConfigOutputTransformer();
			ResponseHandler::render(Config::config(), $transformer);
			break;
		default: // config is read only
			http_response_code(405);
			die('We were unable to understand your request.');
	}
} catch(Throwable $t) {
	ResponseHandler::setResponseCode($t);
	$message = $t->getMessage();
	if (empty($message)) {
		$message = ResponseHandler::GENERIC_ERROR_MESSAGE;
	}
	die($message);
}
