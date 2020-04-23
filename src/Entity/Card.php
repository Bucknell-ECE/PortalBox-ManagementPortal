<?php

namespace Portalbox\Entity;

/**
 * Cards come in a number of types and when presented to a portalbox, the
 * portalbox takes action based on the card type.
 * 
 * @package Portalbox\Entity
 */
interface Card {

	/**
	 * Get the type of the card
	 *
	 * @return int - type one of the predefined constants exposed by CardType
	 */
	public function type_id();

}
