<?php

namespace Portalbox\Type;

use Portalbox\Enumeration\CardType;

/**
 * Cards come in a number of types and when presented to a portalbox, the
 * portalbox shuts down when presented with cards of this type.
 */
class ShutdownCard extends Card {
	/** Get the type of the card */
	public function type(): CardType {
		return CardType::SHUTDOWN;
	}
}
