<?php

namespace Portalbox\Transform;

/**
 * InputTransformers take a blob of data and reconstitute a valid Entity object
 * instance from the data
 * 
 * @package Portalbox\Transform
 */
interface InputTransformer {

	/**
	 * Get get an Entity instance from a serialized blob of data
	 */
	public function deserialize(array $data);

}
