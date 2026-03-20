<?php

require '../../../src/bootstrap.php';

use Portalbox\ResponseHandler;
use Portalbox\Service\BadgeService;
use Portalbox\Transform\BadgeLevelTransformer;
use Portalbox\Transform\BadgeReportTransformer;

try {
	switch($_SERVER['REQUEST_METHOD']) {
		case 'GET':
			if(isset($_GET['user_id']) && !empty($_GET['user_id'])) {	// Read
				$user_id = filter_var($_GET['user_id'] ?? '', FILTER_VALIDATE_INT);
				if ($user_id === false) {
					throw new InvalidArgumentException('The user id must be specified as an integer');
				}
				$service = $container->get(BadgeService::class);
				$badges = $service->getBadgesForUser($user_id);
				ResponseHandler::render($badges, new BadgeLevelTransformer());
			} else {
				$service = $container->get(BadgeService::class);
				$report = $service->getBadgesForActiveUsers();
				$transformer = new BadgeReportTransformer();
				// ResponseHandler can't handle an array in a csv field so we'll
				// customize csv rendering via re-implementation here
				switch ($_SERVER['HTTP_ACCEPT']) {
					case 'text/csv':
						header('Content-Type: text/csv');
						$out = fopen('php://output', 'w');
						fputcsv($out, $transformer->get_column_headers());
						foreach ($report as $list_item) {
							$row = $transformer->serialize($list_item);
							$row['badges'] = implode(',', $row['badges']);
							fputcsv($out, array_values($row));
						}
						fclose($out);
						break;
					case 'application/json': // intentional fallthrough; JSON is our default format
					default:
						$transformed = [];
						foreach ($report as $list_item) {
							$transformed[] = $transformer->serialize($list_item);
						}
						$encoded = json_encode($transformed);

						if (false === $encoded) {
							throw new Exception(json_last_error_msg());
						}

						header('Content-Type: application/json');
						echo $encoded;
				}
			}
			break;
		default:
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