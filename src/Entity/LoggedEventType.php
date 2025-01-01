<?php

namespace Portalbox\Entity;

/**
 * LoggedEventType represents the type of a log event.
 *
 * @todo make this an Enum once we drop support for PHP < 8.1
 */
class LoggedEventType {
	/**
	 * A card was presented but misread, not in the system, or was assigned to
	 * a user who did not have permission to use the equipment
	 */
	public const UNSUCCESSFUL_AUTHENTICATION = 1;

	/**
	 * A user or training card activated the equipment
	 */
	public const SUCCESSFUL_AUTHENTICATION = 2;

	/**
	 * The card keeping a portalbox activated was removed, not returned or
	 * replaced with a proxy card thus the portalbox service ended the
	 * equipment activation
	 */
	public const DEAUTHENTICATION = 3;

	/**
	 * A Portalbox started up and became ready
	 */
	public const STARTUP_COMPLETE = 4;

	/**
	 * A shutdown card was presented and the Portalbox service instucted the
	 * Portalbox to shutdown
	 */
	public const PLANNED_SHUTDOWN = 5;

	/**
	 * Determine if the event log type is valid
	 *
	 * @param int type - the type to check
	 * @return bool - true iff the type is valid
	 */
	public static function is_valid(int $type): bool {
		$valid_values = [
			self::UNSUCCESSFUL_AUTHENTICATION,
			self::SUCCESSFUL_AUTHENTICATION,
			self::DEAUTHENTICATION,
			self::STARTUP_COMPLETE,
			self::PLANNED_SHUTDOWN
		];
		if(in_array($type, $valid_values)) {
			return true;
		}

		return false;
	}

	/**
	 * Get the name for the event type
	 *
	 * @param int type_id - the policy id to check
	 * @return string - name for the event type
	 */
	public static function name_for_type(int $type_id) : string {
		switch($type_id) {
			case self::UNSUCCESSFUL_AUTHENTICATION: return 'Failed Authentication';
			case self::SUCCESSFUL_AUTHENTICATION: return 'Activation Session Begun';
			case self::DEAUTHENTICATION: return 'Activation Session Ended';
			case self::STARTUP_COMPLETE: return 'Startup Complete';
			case self::PLANNED_SHUTDOWN: return 'Planned Shutdown';
			default: return 'Invalid';
		}
	}
}
