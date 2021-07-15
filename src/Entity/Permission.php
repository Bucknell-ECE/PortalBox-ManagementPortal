<?php

namespace Portalbox\Entity;

use ReflectionClass;

/**
 * Permission represents a permission which a particular role may have
 * 
 * @package Portalbox\Entity
 */
class Permission {
	/** Users with this permission can create API keys */
	const CREATE_API_KEY = 1;

	/** Users with this permission can read API keys */
	const READ_API_KEY = 2;

	/** Users with this permission can modify API keys */
	const MODIFY_API_KEY = 3;

	/** Users with this permission can delete API keys */
	const DELETE_API_KEY = 4;

	/** Users with this permission can list API keys */
	const LIST_API_KEYS = 5;

	/** Users with this permission can create equipment authorizations */
	const CREATE_EQUIPMENT_AUTHORIZATION = 101;

	/**
	 * Users with this permission can read equipment authorizations
	 * 
	 * Currently unused; Listing is sufficient in the current design
	 */
	//const READ_EQUIPMENT_AUTHORIZATION = 102;

	/**
	 * Users with this permission can modify authorizations
	 * 
	 * Currently unused; users have a list of authorizations... either they
	 * have authorization or they don't. So creating and deleting a user's
	 * authorizations are sufficient in the current model
	 */
	// const MODIFY_AUTHORIZATION = 103;

	/** Users with this permission can delete equipment authorizations */
	const DELETE_EQUIPMENT_AUTHORIZATION = 104;

	/** Users with this permission can list equipment authorizations */
	const LIST_EQUIPMENT_AUTHORIZATIONS = 105;

	/** Users with this permission can list their equipment authorizations */
	const LIST_OWN_EQUIPMENT_AUTHORIZATIONS = 106;

	/**
	 * Users with this permission can create card types
	 *
	 * Currently unused. Card types play a special role in the system as
	 * designed. Code in the IoT Application makes decisions based on the
	 * card type thus types are implemented as constants in the code
	 */
	// const CREATE_CARD_TYPE = 201;

	/**
	 * Users with this permission can read card types
	 *
	 * Currently unused. Card types play a special role in the system as
	 * designed. Code in the IoT Application makes decisions based on the
	 * card type thus types are implemented as constants in the code
	 */
	// const READ_CARD_TYPE = 202;

	/**
	 * Users with this permission can modify card types
	 *
	 * Currently unused. Card types play a special role in the system as
	 * designed. Code in the IoT Application makes decisions based on the
	 * card type thus types are implemented as constants in the code
	 */
	// const MODIFY_CARD_TYPE = 203;

	/**
	 * Users with this permission can delete card types
	 *
	 * Currently unused. Card types play a special role in the system as
	 * designed. Code in the IoT Application makes decisions based on the
	 * card type thus types are implemented as constants in the code
	 */
	// const DELETE_CARD_TYPE = 204;

	/**
	 * Users with this permission can list card types
	 */
	const LIST_CARD_TYPES = 205;

	/** Users with this permission can create equipment access cards */
	const CREATE_CARD = 301;

	/** Users with this permission can read equipment access cards */
	const READ_CARD = 302;

	/** Users with this permission can modify equipment access cards */
	const MODIFY_CARD = 303;

	/** 
	 * Users with this permission can delete equipment access cards
	 *
	 * Currently usused and probably never will be used as our logs have a
	 * foreign key relationship to cards.
	 */
	//const DELETE_CARD = 304;

	/** Users with this permission can list equipment access cards */
	const LIST_CARDS = 305;

	/** Users with this permission can list their own equipment access cards */
	const LIST_OWN_CARDS = 306;

	/** Users with this permission can create charge policies
	 * 
	 * Charge policies play a special role in the system as designed. Stored
	 * Procedures in the database make decisions based on charge policy thus
	 * policies are implemented as constants in code.
	 */
	const CREATE_CHARGE_POLICY = 401;

	/** Users with this permission can read charge policies
	 * 
	 * Charge policies play a special role in the system as designed. Stored
	 * Procedures in the database make decisions based on charge policy thus
	 * policies are implemented as constants in code.
	 */
	const READ_CHARGE_POLICY = 402;

	/** Users with this permission can modify charge policies
	 * 
	 * Charge policies play a special role in the system as designed. Stored
	 * Procedures in the database make decisions based on charge policy thus
	 * policies are implemented as constants in code.
	 */
	const MODIFY_CHARGE_POLICY = 403;

	/** Users with this permission can delete charge policies
	 * 
	 * Charge policies play a special role in the system as designed. Stored
	 * Procedures in the database make decisions based on charge policy thus
	 * policies are implemented as constants in code.
	 */
	const DELETE_CHARGE_POLICY = 404;

	/** Users with this permission can list charge policies */
	const LIST_CHARGE_POLICIES = 405;

	/** Users with this permission can create charges */
	const CREATE_CHARGE = 501;

	/** Users with this permission can read charges */
	const READ_CHARGE = 502;

	/** Users with this permission can modify charges */
	const MODIFY_CHARGE = 503;

	/** Users with this permission can delete charges */
	const DELETE_CHARGE = 504;

	/** Users with this permission can list charges */
	const LIST_CHARGES = 505;

	/** Users with this permission can list their own charges */
	const LIST_OWN_CHARGES = 506;

	// CONFIG for now does not have permissions

	/** Users with this permission can create equipment types */
	const CREATE_EQUIPMENT_TYPE = 601;

	/** Users with this permission can read equipment types */
	const READ_EQUIPMENT_TYPE = 602;

	/** Users with this permission can modify equipment types */
	const MODIFY_EQUIPMENT_TYPE = 603;

	/** Users with this permission can delete equipment types */
	const DELETE_EQUIPMENT_TYPE = 604;

	/** Users with this permission can list equipment types */
	const LIST_EQUIPMENT_TYPES = 605;

	/** Users with this permission can create equipment */
	const CREATE_EQUIPMENT = 701;

	/** Users with this permission can read equipment */
	const READ_EQUIPMENT = 702;

	/** Users with this permission can modify equipment */
	const MODIFY_EQUIPMENT = 703;

	/** Users with this permission can delete equipment */
	const DELETE_EQUIPMENT = 704;

	/** Users with this permission can list equipment */
	const LIST_EQUIPMENT = 705;

	/** Users with this permission can create locations */
	const CREATE_LOCATION = 801;

	/** Users with this permission can read locations */
	const READ_LOCATION = 802;

	/** Users with this permission can modify locations */
	const MODIFY_LOCATION = 803;

	/** Users with this permission can delete locations */
	const DELETE_LOCATION = 804;

	/** Users with this permission can list locations */
	const LIST_LOCATIONS = 805;

	/**
	 * Users with this permission can create log entries
	 *
	 * Currently usused
	 */
	//const CREATE_LOG = 901;

	/** Users with this permission can read log entries */
	const READ_LOG = 902;

	/**
	 * Users with this permission can modify log entries
	 *
	 * Currently usused and probably never will be used as our logs are
	 * designed to be append only
	 */
	//const MODIFY_LOG = 903;

	/**
	 * Users with this permission can delete log entries
	 *
	 * Currently usused and probably never will be used as our logs are
	 * designed to be append only
	 */
	//const DELETE_LOG = 904;

	/** Users with this permission can list log entries */
	const LIST_LOGS = 905;

	/** Users with this permission can create payments */
	const CREATE_PAYMENT = 1001;

	/** Users with this permission can read payments */
	const READ_PAYMENT = 1002;

	/** Users with this permission can modify payments */
	const MODIFY_PAYMENT = 1003;

	/** Users with this permission can delete payments */
	const DELETE_PAYMENT = 1004;

	/** Users with this permission can list payments */
	const LIST_PAYMENTS = 1005;

	/** Users with this permission can list their payments */
	const LIST_OWN_PAYMENTS = 1006;

	/** Users with this permission can create roles */
	const CREATE_ROLE = 1101;

	/** Users with this permission can read roles */
	const READ_ROLE = 1102;

	/** Users with this permission can modify roles */
	const MODIFY_ROLE = 1103;

	/** Users with this permission can delete roles */
	const DELETE_ROLE = 1104;

	/** Users with this permission can list roles */
	const LIST_ROLES = 1105;

	/** Users with this permission can view the roles page */
	const VIEW_ROLES = 1107;
	
	/** Users with this permission can create users */
	const CREATE_USER = 1201;

	/** Users with this permission can read users */
	const READ_USER = 1202;

	/** Users with this permission can modify users */
	const MODIFY_USER = 1203;

	/** Users with this permission can delete users */
	const DELETE_USER = 1204;

	/** Users with this permission can list users */
	const LIST_USERS = 1205;

	/** Users with this permission can read their own user record */
	const READ_OWN_USER = 1206;

	/**
	 * Determine if the permission is valid
	 *
	 * @param int permission - the permission to check
	 * @return bool - true iff the permission is valid
	 */
	public static function is_valid(int $permission) {
		$valid_values = array_values((new ReflectionClass(get_class()))->getConstants());
		if(in_array($permission, $valid_values)) {
			return true;
		}

		return false;
	}
}
