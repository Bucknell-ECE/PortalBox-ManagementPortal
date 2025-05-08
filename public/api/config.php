<?php
// Force display of errors (add these lines)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log to a custom file we can access (add these lines)
ini_set('error_log', '/tmp/api_debug.log');
error_log("API Execution Started: " . date('Y-m-d H:i:s'));

// Wrap everything in a try-catch (add this line)
try {
    // Your original code continues here...
    // Don't modify this part yet, just wrap it in the try block
    
    // After all your original code, add the closing catch block
} catch (Throwable $e) {
    error_log("Caught exception: " . $e->getMessage() . " | Stack trace: " . $e->getTraceAsString());
    echo "API Error: " . $e->getMessage(); // This will show in the response
    header("HTTP/1.1 500 Internal Server Error");
    exit;
}
// configuration needs to be accessible before the front end can
// authenticate as this is how we inject OAUTH API Keys etc. We want to
// restrict access to javascript that was served from this server though

require '../../src/autoload.php';

use Portalbox\Config;
use Portalbox\ResponseHandler;

use Portalbox\Transform\ConfigOutputTransformer;

switch($_SERVER['REQUEST_METHOD']) {
	case 'GET':
		try {
			$transformer = new ConfigOutputTransformer();
			ResponseHandler::render(Config::config(), $transformer);
		} catch(Exception $e) {
			http_response_code(500);
			die('Unable to read settings from config file.');
		}
		break;
	default: // config is read only
		http_response_code(405);
		die('We were unable to understand your request.');
}
?>
