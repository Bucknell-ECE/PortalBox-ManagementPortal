<?php

namespace Portalbox\Transform;

use Portalbox\Enumeration\CardType;

class CardTypeTransformer implements OutputTransformer {
	public function serialize($data, bool $traverse = false): array {
		return [
			'id' => $data->value,
			'name' => $data->name()
		];
	}

	public function get_column_headers(): array {
		return ['id', 'Name'];
	}
}
