<?php

namespace Portalbox\Enumeration;

/**
 * ChargePolicy represents how the equipment type charge rate in turned into a
 * charge when an activation session ends... as a stored procedure in the
 * database does the calculation, ChargePolicies occupy a privileged role
 * and are difficult to change so they are predefined
 */
enum ChargePolicy: int {
	/** This charge type indicates that a charge has been manually adjusted. */
	case MANUALLY_ADJUSTED = 1;

	/**
	 * Equipment Types with this Charge type do not generate a charge
	 * when the portalbox signals the end of an activation
	 */
	case NO_CHARGE = 2;

	/**
	 * Equipment Types with this charge type create a charge that is 1 times
	 * the rate when the portalbox signals the end of an activation
	 */
	case PER_USE = 3;

	/**
	 * Equipment Types with this charge type create a charge that is session
	 * duration in minutes times the rate when the portalbox signals the end
	 * of an activation
	 */
	case PER_MINUTE = 4;
}
