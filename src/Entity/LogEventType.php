<?php

namespace Portalbox\Entity;

use ReflectionClass;

/**
 * LogEventType represents the type of a log event.
 * 
 * @package Portalbox\Entity
 */
class LogEventType {
	/**
	 * A card was presented but misread, not in the system, or was assigned to
	 * a user who did not have permission to use the equipment
	 */
	const UNSUCESSFUL_AUTHENTICATION = 1;

	/**
	 * A user or training card activated the equipment
	 */
	const SUCESSFUL_AUTHENTICATION = 2;

	/** 
	 * The card keeping a portalbox activated was removed, not returned or
	 * replaced with a proxy card thus the portalbox service ended the
	 * equipment activation
	 */
	const DEAUTHENTICATION = 3;

	/**
	 * A Portalbox started up and became ready
	 */
	const STARTUP_COMPLETE = 4;

	/**
	 * A shutdown card was presented and the Portalbox service instucted the
	 * Portalbox to shutdown
	 */
	const PLANNED_SHUTDOWN = 5;

	/**
	 * Determine if the event log type is valid
	 *
	 * @param int type - the type to check
	 * @return bool - true iff the type is valid
	 */
	public static function is_valid(int $type) {
		$valid_values = array_values((new ReflectionClass(get_class()))->getConstants());
		if(in_array($type, $valid_values)) {
			return true;
		}

		return false;
	}
}
