<?php

namespace Portalbox\Entity;

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
}
