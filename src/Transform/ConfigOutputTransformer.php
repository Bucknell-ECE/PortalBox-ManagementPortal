<?php

namespace Portalbox\Transform;

use DomainException;

/**
 * ConfigTransformer is our bridge between the Config and the outputable portion
 * 
 * @package Portalbox\Transform
 */
class ConfigOutputTransformer implements OutputTransformer {

	/**
	 * Called to serialize a Config
	 *
	 * @param bool $traverse - traverse the object graph if true, otherwise 
	 *      may substitute flattened representations where appropriate.
	 * @return array -  a dictionary whose values are null, string, int, float
	 *      dictionaries, or arrays with the compound types having the same
	 *      restrictions when $traverse is true or a dictionary whose values
	 *      are null, string, int, and float otherwise
	 */
	public function serialize($config, bool $traverse = false) : array {
		return $config->web_ui_settings();
	}

	/**
	 * Called to get the column headers for a tabular output format eg csv.
	 * The column count should match the number of fields in an array returned
	 * by serialize() when $traverse is false
	 * 
	 * @return array - a list of strings that ccan be column headers
	 */
	public function get_column_headers() : array {
		throw new DomainException('Data does not support this operation');
	}
}
