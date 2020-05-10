<?php

namespace Portalbox;

use InvalidArgumentException;

use Portalbox\Transform\NullOutputTransformer;
use Portalbox\Transform\OutputTransformer;

/**
 * OuptputTransformer - Is used to send encoded responses to a requestor. Can
 * be subclassed to control the fields exposed in the response. Subclasses
 * may override json_encode_entity, json_encode_list, get_cvs_header,
 * list_item_to_array.
 *
 * @package Portalbox\Transformer
 */
class ResponseHandler {

	/**
	 * Encodes and sends data to the requestor
	 *
	 * @param AbstractEntity|array data - an Entity or list of entities to
	 *     render into an HTTP response
	 * @param OutputTransformer|null transformer - a transformer which can
	 *     transform the $data entity object into a dictionary whose values
	 *     are null, string, int, float, dictionaries, or arrays with the
	 *     compound types having the same restrictions when $traverse is
	 *     true or a dictionary whose values are null, string, int, and float
	 *     otherwise
	 */
	public static function render($data, OutputTransformer $transformer = null) {
		if(NULL === $transformer) {
			$transformer = new NullOutputTransformer();
		}

		if(NULL === $data) {
			throw new InvalidArgumentException('Unable to transform NULL value into response data');
		} else if(is_array($data)) {
			self::render_list_response($transformer, $data);
		} else {
			self::render_entity_response($transformer, $data);
		}
	}

	/**
	 * Decide on the encoding for the response and render the data into
	 * the response accordingly
	 *
	 * @param array $data - the list of entity instances to render into the response
	 */
	private static function render_list_response(OutputTransformer $transformer, $data) {
		// check request for desired encoding
		switch($_SERVER['HTTP_ACCEPT']) {
			case 'text/csv':
				header('Content-Type: text/csv');
				$out = fopen('php://output', 'w');
				fputcsv($out, $transformer->get_column_headers());
				foreach($data as $list_item) {
					fputcsv($out, array_values($transformer->serialize($list_item)));
				}
				fclose($out);
				break;
			case 'application/json':
			default:
				$transformed = [];
				foreach($data as $list_item) {
					$transformed[] = $transformer->serialize($list_item);
				}
				$encoded = json_encode($transformed);

				if(false !== $encoded) {
					header('Content-Type: application/json');
					echo $encoded;
				} else {
					http_response_code(500);
					die(json_last_error_msg());
				}
		}
	}

	/**
	 * Render the data into the response
	 *
	 * @param AbstractEntity $data - the entity instance to render into the response
	 */
	private static function render_entity_response(OutputTransformer $transformer, $data) {
		$encoded = json_encode($transformer->serialize($data, true));

		if(false !== $encoded) {
			header('Content-Type: application/json');
			echo $encoded;
		} else {
			http_response_code(500);
			die(json_last_error_msg());
		}
	}
}
