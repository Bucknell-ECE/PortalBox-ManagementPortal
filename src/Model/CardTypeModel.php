<?php

namespace Portalbox\Model;

use Portalbox\Entity\CardType;
use Portalbox\Exception\DatabaseException;

use PDO;

class CardTypeModel extends AbstractModel {
    public function search() : ?array {
        $connection = $this->configuration()->readonly_db_connection();
        $sql = 'SELECT id, name FROM card_types';
        $statement = $connection->prepare($sql);
        if($statement->execute()) {
            $data = $statement->fetchAll(PDO::FETCH_ASSOC);
            if(FALSE !== $data) {
                return $this->buildCardTypesFromArrays($data);
            } else {
                return null;
            }
        } else {
            throw new DatabaseExcpetion($connection->errorInfo()[2]);
        }
    }

    private function buildCardTypesFromArray(array $data) : CardType {
        return (new CardType())
            ->set_id($data['id']);
    }

    private function buildCardTypesFromArrays(array $data) : array {
        $card_types = array();

        foreach($data as $datum) {
            $card_types[] = $this->buildCardTypesFromArray($datum);
        }

        return $card_types;
    }
}