<?php
// configuration needs to be accessible before the front end can
// authenticate as this is how we inject OAUTH API Keys etc. We want to
// restrict access to javascript that was served from this server though

require '../../src/autoload.php';

use Portalbox\Config;
use Portalbox\Transform\OutputTransformer;

switch($_SERVER['REQUEST_METHOD']) {
	case 'GET':
		try {
			OutputTransformer::render_response(Config::config());
		} catch(Exception $e) {
			http_response_code(500);
			die('Unable to read settings from config file.');
		}
		break;
	default: // config is read only
		http_response_code(405);
		die('We were unable to understand your request.');
}