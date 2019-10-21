<?php
	// configuration needs to be accessible before the front end can
	// authenticate as this is how we inject OAUTH API Keys etc. We want to
	// restrict access to javascript that was served from this server though

	if((include_once '../lib/EncodeOutput.php') === FALSE) {
		header('HTTP/1.0 500 Internal Server Error');
		die('We were unable to load some dependencies. Please ask your server administrator to investigate');
	}

	// load settings
	$settings = parse_ini_file('../config/config.ini', TRUE);
	if($settings != FALSE && array_key_exists('oauth', $settings)) {
		switch($_SERVER['REQUEST_METHOD']) {
			case 'GET':
				// we only want to reply with oauth settings
				render_json($settings['oauth']);
				break;
			default: // config is read only
				header('HTTP/1.0 405 Method Not Allowed');
				die('We were unable to understand your request.');
		}
	} else {
		header('HTTP/1.0 500 Internal Server Error');
		die('Unable to read settings from config file.');
		// say something about contacting admin?
	}
?>