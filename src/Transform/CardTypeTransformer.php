<?php

namespace Portalbox\Transform;

use InvalidArgumentException;
use Portalbox\Type\CardType;

class CardTypeTransformer implements OutputTransformer {
	public function serialize($data, bool $traverse = false): array {
		if ($traverse) {
			return [
				'id' => $data->id(),
				'name' => $data->name()
			];
		} else {
			return [
				'id' => $data->id(),
				'name' => $data->name()
			];
		}
	}

	public function get_column_headers(): array {
		return ['id', 'Name'];
	}
}
