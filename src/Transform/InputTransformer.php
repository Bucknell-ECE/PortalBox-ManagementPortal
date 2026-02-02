<?php

namespace Portalbox\Transform;

/**
 * InputTransformers take a blob of data and reconstitute an object from the data
 */
interface InputTransformer {
	/**
	 * Get get an object from a serialized blob of data
	 */
	public function deserialize(array $data);
}
