<?php

namespace Portalbox\Transform;

/**
 * OuptputTransformer - Is used to send encoded responses to a requestor. Can
 * be subclassed to control the fields exposed in the response. Subclasses
 * may override json_encode_entity, json_encode_list, get_cvs_header,
 * list_item_to_array.
 *
 * @package Portalbox\Transformer
 */
class OutputTransformer {

	/**
	 * Encodes and sends data to the requestor
	 *
	 * @param AbstractEntity|array data - an Entity or list of entities to
	 *     render into an HTTP response
	 */
	public static function render_response($data) {
		if(is_array($data)) {
			self::render_list_response($data);
		} else {
			self::render_entity_response($data);
		}
	}

	/**
	 * Decide on the encoding for the response and render the data into
	 * the response accordingly
	 *
	 * @param array $data - the list of entity instances to render into the response
	 */
	private static function render_list_response($data) {
		// check request for desired encoding
		switch($_SERVER['HTTP_ACCEPT']) {
			case 'text/csv':
				header('Content-Type: text/csv');
				$out = fopen('php://output', 'w');
				fputcsv($out, static::get_cvs_header());
				foreach($data as $list_item) {
					if($list_item instanceof RESTSerializable) {
						fputcsv($out, $list_item->rest_serialize());
					} else {
						fputcsv($out, $list_item); // this can fail badly!!!
					}
				}
				fclose($out);
				break;
			case 'application/json':
			default:
				$transformed = [];
				foreach($data as $list_item) {
					if($list_item instanceof RESTSerializable) {
						$transformed[] = $list_item->rest_serialize();
					} else {
						$transformed[] = $list_item;
					}
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
	private static function render_entity_response($data) {
		$encoded = false;
		if($data instanceof RESTSerializable) {
			$encoded = json_encode($data->rest_serialize(true));
		} else {
			$encoded = json_encode($data);
		}

		if(false !== $encoded) {
			header('Content-Type: application/json');
			echo $encoded;
		} else {
			http_response_code(500);
			die(json_last_error_msg());
		}
	}

	/**
	 * Get a list of column headers
	 *
	 * @param array - an indexed array of strings which can be redered as the
	 *     header row of a csv file
	 */
	protected static function get_cvs_header() : array {
		return array('id');
	}
}
