<?php

namespace Portalbox\Transform;

use InvalidArgumentException;
use Portalbox\Config;
use Portalbox\Model\EquipmentTypeModel;
use Portalbox\Model\LocationModel;
use Portalbox\Type\Equipment;

/**
 * EquipmentTransformer is our bridge between dictionary representations and
 * Equipment instances.
 */
class EquipmentTransformer implements OutputTransformer {
	/**
	 * Called to serialize Equipment instance to a dictionary
	 *
	 * @param bool $traverse - traverse the object graph if true, otherwise
	 *      may substitute flattened representations where appropriate.
	 * @return array -  a dictionary whose values are null, string, int, float
	 *      dictionaries, or arrays with the compound types having the same
	 *      restrictions when $traverse is true or a dictionary whose values
	 *      are null, string, int, and float otherwise
	 */
	public function serialize($data, bool $traverse = false): array {
		if ($traverse) {
			return [
				'id' => $data->id(),
				'name' => $data->name(),
				'type_id' => $data->type_id(),
				'type' => $data->type()->name(),
				'mac_address' => is_null($data->mac_address()) ? null : $data->mac_address(),
				'location_id' => $data->location_id(),
				'location' => $data->location()->name(),
				'timeout' => $data->timeout(),
				'in_service' => $data->is_in_service(),
				'in_use' => $data->is_in_use(),
				'service_minutes' => $data->service_minutes(),
				'ip_address' => $data->ip_address()
			];
		} else {
			return [
				'id' => $data->id(),
				'name' => $data->name(),
				'type' => $data->type()->name(),
				'mac_address' => is_null($data->mac_address()) ? '' : $data->mac_address(),
				'location' => $data->location()->name(),
				'timeout' => $data->timeout(),
				'in_service' => $data->is_in_service(),
				'in_use' => $data->is_in_use(),
				'service_minutes' => $data->service_minutes(),
				'ip_address' => $data->ip_address()
			];
		}
	}

	/**
	 * Called to get the column headers for a tabular output format eg csv.
	 * The column count should match the number of fields in an array returned
	 * by serialize() when $traverse is false
	 *
	 * @return array - a list of strings that can be column headers
	 */
	public function get_column_headers(): array {
		return ['id', 'Name', 'Type', 'MAC Address', 'Location', 'Timeout', 'In Service', 'In Use', 'Service Minutes'];
	}
}
