<?php

namespace Portalbox\Entity;

/**
 * Cards come in a number of types and when presented to a portalbox, the
 * portalbox shutsdown when presented with cards of this type.
 * 
 * @package Portalbox\Entity
 */
class ProxyCard extends AbstractEntity implements Card {

	/**
	 * Get the type of the card
	 *
	 * @return int - type one of the predefined constants exposed by CardType
	 */
	public function type_id() : int {
		return CardType::PROXY;
	}

}
