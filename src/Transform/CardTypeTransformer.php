<?php

namespace Portalbox\Transform;

use InvalidArgumentException;
use Portalbox\Entity\CardType;

class CardTypeTransformer implements InputTransformer, OutputTransformer {
    public function deserialize(array $data) : CardType {
        if(!array_key_exists('id', $data)) {
            throw new InvalidArgumentException('\'id\' is a required field');
        }

        return (new CardType())
            ->set_id($data['id']);
    }

    public function serialize($data, bool $traverse = false) : array {
        if($traverse) {
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

    public function get_column_headers() : array {
        return ['id', 'Name'];
    }
}
