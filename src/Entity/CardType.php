<?php

namespace Portalbox\Entity;

use ReflectionClass;

/**
 * CardType represents the kind of an equipment activation card... the IoT
 * application decides what to do based on CardType when presented with a card.
 * 
 * @package Portalbox\Entity
 */
class CardType {
	/** This card type can be used to shutdown Portalboxes */
	const SHUTDOWN = 1;

	/**
	 * This card type can be used to keep a portalbox activated after the user
	 * card which activated it has been removed.
	 */
	const PROXY = 2;

	/** This card type can be used when training a user to use equipment */
	const TRAINING = 3;

	/** This card type is issued to users so they may activate a Portalbox */
	const USER = 4;

	protected $id;

	protected $name;

	public function name() : string {
		return $this->name;
	}

	private function set_name(string $name) : self {
		if(0 < strlen($name)) {
			$this->name = $name;
			return $this;
		}
	}

	public function id() : int {
		return $this->id;
	}

	public function set_id(int $id) : self {
		$this->id = $id;
		$this->set_name(CardType::name_for_type($id));
		return $this;
	}

	/**
	 * Determine if the card type is valid
	 *
	 * @param int type - the type to check
	 * @return bool - true iff the type is valid
	 */
	public static function is_valid(int $type) {
		$valid_values = array_values((new ReflectionClass(get_class()))->getConstants());
		if(in_array($type, $valid_values)) {
			return true;
		}

		return false;
	}

	/**
	 * Get the name for the card type
	 * 
	 * @param int type_id - the policy id to check
	 * @return string - name for the event type
	 */
	public static function name_for_type(int $type_id) : string {
		switch($type_id) {
			case self::SHUTDOWN: return 'Shutdown Card';
			case self::PROXY: return 'Proxy Card';
			case self::TRAINING: return 'Training Card';
			case self::USER: return 'User Card';
			default: return 'Invalid';
		}
	}
}
