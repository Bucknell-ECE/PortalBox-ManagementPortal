<?php

namespace Portalbox\Enumeration;


/**
 * CardType represents the kind of an equipment activation card... the IoT
 * application decides what to do based on CardType when presented with a card.
 */
enum CardType: int {
	/** This card type can be used to shutdown Portalboxes */
	case SHUTDOWN = 1;

	/**
	 * This card type can be used to keep a portalbox activated after the user
	 * card which activated it has been removed.
	 */
	case PROXY = 2;

	/** This card type can be used when training a user to use equipment */
	case TRAINING = 3;

	/** This card type is issued to users so they may activate a Portalbox */
	case USER = 4;

	/**
	 * Get the name for the card type
	 *
	 * @return string  the name for the card type
	 */
	public function name(): string {
		return match($this) {
			CardType::SHUTDOWN => 'Shutdown Card',
			CardType::PROXY => 'Proxy Card',
			CardType::TRAINING => 'Training Card',
			CardType::USER => 'User Card',
			default => 'Invalid'
		};
	}
}
