<?php

namespace Portalbox\Transform;

/**
 * Implementors can Serialzable to a limited set of types which can be reliably
 * Transformed into a variety of output encodings
 * 
 * @package Portalbox\Transform
 */
interface RESTSerializable {

	/**
	 * Called to serialize an entity
	 * 
	 * REST services end up with two output modes a single element and a list.
	 * In the single element mode, the whole object graph is normally desired
	 * while in the list mode a simplified graph is typically preferred. This
	 * dicotomy is supported with the $traverse flag. Implementors should give
	 * fuller representations including decending into the object graph when
	 * $traverse is true and limiting output to a flat structure when false...
	 * typically the serialize entity is to be presented in a table or list
	 * when $traverse is false.
	 *
	 * @param bool $traverse - traverse the object graph if true, otherwise 
	 *      may substitute flattened representations where appropriate.
	 * @return array -  a dictionary whose values are null, string, int, float
	 *      dictionaries, or arrays with the compound types having the same
	 *      restrictions
	 */
	public function rest_serialize(bool $traverse = false);

}
