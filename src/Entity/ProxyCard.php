<?php

namespace Portalbox\Entity;

/**
 * Users are typically issued just one card but might be permitted to operate
 * more than one piece of equipment at a time. A 3D printer for instance may run
 * while the user uses other equipment. In this case a user presents their card
 * to activate the equipment and then replaces their card with a card of this
 * type so the equipment will remain in operation while they use their card
 * elsewhere.
 */
class ProxyCard extends Card {
	/** Get the type of the card */
	public function type_id() : int {
		return CardType::PROXY;
	}
}
