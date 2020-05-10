<?php

namespace Portalbox\Entity;

use ReflectionClass;

/**
 * ChargePolicy represents how the equipment type charge rate in turned into a
 * charge when an activation session ends... as a stored procedure in the
 * database does the calculation, ChargePolicies occupy a privleged role
 * and are difficult to change so they are predefined
 * 
 * @package Portalbox\Entity
 */
class ChargePolicy {
	/** This charge type indicates that a charge has been manually adjusted. */
	const MANUALLY_ADJUSTED = 1;

	/**
	 * Equipment Types with this Charge type do not generate a charge
	 * when the portalbox signals the end of an activation
	 */
	const NO_CHARGE = 2;

	/**
	 * Equipment Types with this charge type create a charge that is 1 times
	 * the rate when the portalbox signals the end of an activation
	 */
	const PER_USE = 3;

	/**
	 * Equipment Types with this charge type create a charge that is session
	 * duration in minutes times the rate when the portalbox signals the end
	 * of an activation
	 */
	const PER_MINUTE = 4;

	/**
	 * Determine if the policy id is valid
	 *
	 * @param int policy_id - the policy id to check
	 * @return bool - true iff the policy id is valid
	 */
	public static function is_valid(int $policy_id) {
		$valid_values = array_values((new ReflectionClass(get_class()))->getConstants());
		if(in_array($policy_id, $valid_values)) {
			return true;
		}

		return false;
	}

	/**
	 * Get the name for the charge policy
	 * 
	 * @param int policy_id - the policy id to check
	 * @return string - name for the charge policy
	 */
	public static function name_for_policy(int $policy_id) : string {
		switch($policy_id) {
			case self::MANUALLY_ADJUSTED: return 'Manually Adjusted';
			case self::NO_CHARGE: return 'No Charge';
			case self::PER_USE: return 'Per Use';
			case self::PER_MINUTE: return 'Per Minute';
			default: return 'Invalid';
		}
	}
}