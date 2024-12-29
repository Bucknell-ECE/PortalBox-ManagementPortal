<?php

namespace Portalbox\Entity;

/**
 * Cards come in a number of types and when presented to a portalbox, the
 * portalbox takes action based on the card type.
 */
abstract class Card {
	use \Portalbox\Trait\HasIdProperty;

	/**
	 * Get the type of the card. Must be one of the predefined constants exposed
	 * by CardType
	 */
	abstract public function type_id(): int;

}