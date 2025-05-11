<?php

namespace Portalbox\Entity;

/**
 * Cards come in a number of types and when presented to a portalbox, the
 * portalbox shutsdown when presented with cards of this type.
 */
class ShutdownCard extends Card {
	/** Get the type of the card */
	public function type_id() : int {
		return CardType::SHUTDOWN;
	}
}
