<?php

namespace Portalbox\Entity;

use ReflectionClass;

/**
 * CardType represents the kind of an equipment activation card... the IoT
 * application decides what to do based on CardType when presented with a card.
 *
 * @todo make this an Enum once we drop support for PHP < 8.1
 */
class CardType {
	/** This card type can be used to shutdown Portalboxes */
	public const SHUTDOWN = 1;

	/**
	 * This card type can be used to keep a portalbox activated after the user
	 * card which activated it has been removed.
	 */
	public const PROXY = 2;

	/** This card type can be used when training a user to use equipment */
	public const TRAINING = 3;

	/** This card type is issued to users so they may activate a Portalbox */
	public const USER = 4;

	/** The card type's id */
	protected int $id = -1;

	/** The card type's human readable name */
	protected string $name = 'Invalid';

	/** Get the card type's human readable name */
	public function name() : string {
		return $this->name;
	}

	/** Set the card type's human readable name */
	private function set_name(string $name) : self {
		if(0 < strlen($name)) {
			$this->name = $name;
			return $this;
		}
	}

	/** Get the card type's id */
	public function id() : int {
		return $this->id;
	}

	/** Set the card type's id */
	public function set_id(int $id) : self {
		$this->id = $id;
		$this->set_name(CardType::name_for_type($id));
		return $this;
	}

	/**
	 * Determine if the card type is valid
	 *
	 * @param int type  the type to check
	 * @return bool  true iff the type is valid
	 */
	public static function is_valid(int $type): bool {
		$valid_values = array_values((new ReflectionClass(get_class()))->getConstants());
		if(in_array($type, $valid_values)) {
			return true;
		}

		return false;
	}

	/**
	 * Get the name for the card type
	 *
	 * @param int type_id  the id of the card type
	 * @return string  the name for the card type
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
