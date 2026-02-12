<?php

namespace Portalbox\Type;

use Portalbox\Enumeration\CardType;

/**
 * Cards come in a number of types and when presented to a portalbox, the
 * portalbox takes action based on the card type.
 */
abstract class Card {
	use \Portalbox\Trait\HasIdProperty;

	/**
	 * Get the type of the card.
	 */
	abstract public function type(): CardType;
}
