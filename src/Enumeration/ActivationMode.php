<?php

namespace Portalbox\Enumeration;

/**
 * When a portalbox is presented with a card it asks the website whether the
 * card is acceptable. However it needs a bit more information than just yes or
 * no, it needs to also know why the card was acceptable. For that we return
 * ActivationMode
 */
enum ActivationMode: string {
	/**
	 * Used when the card presented belongs to an authorized user. Also used if
	 * the activating user's card is returned.
	 */
	case AUTHORIZED_USER = 'user';

	/** Used when a proxy card is accepted */
	case PROXY = 'proxy';

	/**
	 * Used when the card belongs to an unauthorized user but the portalbox was
	 * activated by a user permitted to train users
	 */
	case TRAINING = 'training';
}
