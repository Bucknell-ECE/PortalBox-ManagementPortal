<?php

namespace Portalbox\Enumeration;

/**
 * LoggedEventType represents the type of a log event.
 */
enum LoggedEventType: int {
	/**
	 * A card was presented but misread, not in the system, or was assigned to
	 * a user who did not have permission to use the equipment
	 */
	case UNSUCCESSFUL_AUTHENTICATION = 1;

	/**
	 * A user or training card activated the equipment
	 */
	case SUCCESSFUL_AUTHENTICATION = 2;

	/**
	 * The card keeping a portalbox activated was removed, not returned or
	 * replaced with a proxy card thus the portalbox service ended the
	 * equipment activation
	 */
	case DEAUTHENTICATION = 3;

	/**
	 * A Portalbox started up and became ready
	 */
	case STARTUP_COMPLETE = 4;

	/**
	 * A shutdown card was presented and the Portalbox service instructed the
	 * Portalbox to shutdown
	 */
	case PLANNED_SHUTDOWN = 5;

	/** A user training began */
	case TRAINING = 6;

	/**
	 * Get the name for the event type
	 */
	public function name(): string {
		return match ($this) {
			LoggedEventType::UNSUCCESSFUL_AUTHENTICATION => 'Failed Authentication',
			LoggedEventType::SUCCESSFUL_AUTHENTICATION => 'Activation Session Begun',
			LoggedEventType::DEAUTHENTICATION => 'Activation Session Ended',
			LoggedEventType::STARTUP_COMPLETE => 'Startup Complete',
			LoggedEventType::PLANNED_SHUTDOWN => 'Planned Shutdown',
			LoggedEventType::TRAINING => 'Training'
		};
	}
}
