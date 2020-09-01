<?php

namespace Portalbox\Transform;

use InvalidArgumentException;

use Portalbox\Config;

// violation of SOLID design... should use these via interfaces and dependency injection
use Portalbox\Model\EquipmentTypeModel;

/**
 * AuthorizationsTransformer is our bridge between dictionary representations
 * of Authorizations lists and arrays of equipment ids
 * 
 * @package Portalbox\Transform
 */
class AuthorizationsTransformer implements InputTransformer {
	/**
	 * Deserialize an Authorizations entity object from a dictionary
	 * 
	 * @param array data - a dictionary representing an Authorizations list
	 * @return array<int> - a list of authorization ids
	 * @throws InvalidArgumentException if a required field is not specified
	 */
	public function deserialize(array $data) : array {
		if(!array_key_exists('authorizations', $data)) {
			throw new InvalidArgumentException('\'authorizations\' is a required field');
		}
		if(!is_array($data['authorizations'])) {
			throw new InvalidArgumentException('\'authorizations\' must be a list of equipment type ids');
		}
		$model = new EquipmentTypeModel(Config::config());
		foreach($data['authorizations'] as $equipment_type_id) {
			$equipment_type = $model->read($equipment_type_id);
			if(NULL === $equipment_type) {
				throw new InvalidArgumentException('\'authorizations\' must be a list of equipment type ids');
			}
		}

		return $data['authorizations'];
	}
}