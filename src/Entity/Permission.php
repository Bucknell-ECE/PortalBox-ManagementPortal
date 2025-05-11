<?php

namespace Portalbox\Entity;

/**
 * Permission represents a permission which a particular role may have
 *
 * @todo make this an Enum once we drop support for PHP < 8.1
 */
class Permission {
	/** Users with this permission can create API keys */
	public const CREATE_API_KEY = 1;

	/** Users with this permission can read API keys */
	public const READ_API_KEY = 2;

	/** Users with this permission can modify API keys */
	public const MODIFY_API_KEY = 3;

	/** Users with this permission can delete API keys */
	public const DELETE_API_KEY = 4;

	/** Users with this permission can list API keys */
	public const LIST_API_KEYS = 5;

	/** Users with this permission can create equipment authorizations */
	public const CREATE_EQUIPMENT_AUTHORIZATION = 101;

	/**
	 * Users with this permission can read equipment authorizations
	 *
	 * Currently unused; Listing is sufficient in the current design
	 */
	//public const READ_EQUIPMENT_AUTHORIZATION = 102;

	/**
	 * Users with this permission can modify authorizations
	 *
	 * Currently unused; users have a list of authorizations... either they
	 * have authorization or they don't. So creating and deleting a user's
	 * authorizations are sufficient in the current model
	 */
	// public const MODIFY_AUTHORIZATION = 103;

	/** Users with this permission can delete equipment authorizations */
	public const DELETE_EQUIPMENT_AUTHORIZATION = 104;

	/** Users with this permission can list equipment authorizations */
	public const LIST_EQUIPMENT_AUTHORIZATIONS = 105;

	/** Users with this permission can list their equipment authorizations */
	public const LIST_OWN_EQUIPMENT_AUTHORIZATIONS = 106;

	/**
	 * Users with this permission can create card types
	 *
	 * Currently unused. Card types play a special role in the system as
	 * designed. Code in the IoT Application makes decisions based on the
	 * card type thus types are implemented as constants in the code
	 */
	// public const CREATE_CARD_TYPE = 201;

	/**
	 * Users with this permission can read card types
	 *
	 * Currently unused. Card types play a special role in the system as
	 * designed. Code in the IoT Application makes decisions based on the
	 * card type thus types are implemented as constants in the code
	 */
	// public const READ_CARD_TYPE = 202;

	/**
	 * Users with this permission can modify card types
	 *
	 * Currently unused. Card types play a special role in the system as
	 * designed. Code in the IoT Application makes decisions based on the
	 * card type thus types are implemented as constants in the code
	 */
	// public const MODIFY_CARD_TYPE = 203;

	/**
	 * Users with this permission can delete card types
	 *
	 * Currently unused. Card types play a special role in the system as
	 * designed. Code in the IoT Application makes decisions based on the
	 * card type thus types are implemented as constants in the code
	 */
	// public const DELETE_CARD_TYPE = 204;

	/**
	 * Users with this permission can list card types
	 */
	public const LIST_CARD_TYPES = 205;

	/** Users with this permission can create equipment access cards */
	public const CREATE_CARD = 301;

	/** Users with this permission can read equipment access cards */
	public const READ_CARD = 302;

	/** Users with this permission can modify equipment access cards */
	public const MODIFY_CARD = 303;

	/**
	 * Users with this permission can delete equipment access cards
	 *
	 * Currently unused and probably never will be used as our logs have a
	 * foreign key relationship to cards.
	 */
	//public const DELETE_CARD = 304;

	/** Users with this permission can list equipment access cards */
	public const LIST_CARDS = 305;

	/** Users with this permission can list their own equipment access cards */
	public const LIST_OWN_CARDS = 306;

	/** Users with this permission can create charge policies
	 *
	 * Charge policies play a special role in the system as designed. Stored
	 * Procedures in the database make decisions based on charge policy thus
	 * policies are implemented as constants in code.
	 */
	public const CREATE_CHARGE_POLICY = 401;

	/** Users with this permission can read charge policies
	 *
	 * Charge policies play a special role in the system as designed. Stored
	 * Procedures in the database make decisions based on charge policy thus
	 * policies are implemented as constants in code.
	 */
	public const READ_CHARGE_POLICY = 402;

	/** Users with this permission can modify charge policies
	 *
	 * Charge policies play a special role in the system as designed. Stored
	 * Procedures in the database make decisions based on charge policy thus
	 * policies are implemented as constants in code.
	 */
	public const MODIFY_CHARGE_POLICY = 403;

	/** Users with this permission can delete charge policies
	 *
	 * Charge policies play a special role in the system as designed. Stored
	 * Procedures in the database make decisions based on charge policy thus
	 * policies are implemented as constants in code.
	 */
	public const DELETE_CHARGE_POLICY = 404;

	/** Users with this permission can list charge policies */
	public const LIST_CHARGE_POLICIES = 405;

	/** Users with this permission can create charges */
	public const CREATE_CHARGE = 501;

	/** Users with this permission can read charges */
	public const READ_CHARGE = 502;

	/** Users with this permission can modify charges */
	public const MODIFY_CHARGE = 503;

	/** Users with this permission can delete charges */
	public const DELETE_CHARGE = 504;

	/** Users with this permission can list charges */
	public const LIST_CHARGES = 505;

	/** Users with this permission can list their own charges */
	public const LIST_OWN_CHARGES = 506;

	// CONFIG for now does not have permissions

	/** Users with this permission can create equipment types */
	public const CREATE_EQUIPMENT_TYPE = 601;

	/** Users with this permission can read equipment types */
	public const READ_EQUIPMENT_TYPE = 602;

	/** Users with this permission can modify equipment types */
	public const MODIFY_EQUIPMENT_TYPE = 603;

	/** Users with this permission can delete equipment types */
	public const DELETE_EQUIPMENT_TYPE = 604;

	/** Users with this permission can list equipment types */
	public const LIST_EQUIPMENT_TYPES = 605;

	/** Users with this permission can create equipment */
	public const CREATE_EQUIPMENT = 701;

	/** Users with this permission can read equipment */
	public const READ_EQUIPMENT = 702;

	/** Users with this permission can modify equipment */
	public const MODIFY_EQUIPMENT = 703;

	/** Users with this permission can delete equipment */
	public const DELETE_EQUIPMENT = 704;

	/** Users with this permission can list equipment */
	public const LIST_EQUIPMENT = 705;

	/** Users with this permission can create locations */
	public const CREATE_LOCATION = 801;

	/** Users with this permission can read locations */
	public const READ_LOCATION = 802;

	/** Users with this permission can modify locations */
	public const MODIFY_LOCATION = 803;

	/** Users with this permission can delete locations */
	public const DELETE_LOCATION = 804;

	/** Users with this permission can list locations */
	public const LIST_LOCATIONS = 805;

	/**
	 * Users with this permission can create log entries
	 *
	 * Currently unused
	 */
	//public const CREATE_LOG = 901;

	/** Users with this permission can read log entries */
	public const READ_LOG = 902;

	/**
	 * Users with this permission can modify log entries
	 *
	 * Currently unused and probably never will be used as our logs are
	 * designed to be append only
	 */
	//public const MODIFY_LOG = 903;

	/**
	 * Users with this permission can delete log entries
	 *
	 * Currently unused and probably never will be used as our logs are
	 * designed to be append only
	 */
	//public const DELETE_LOG = 904;

	/** Users with this permission can list log entries */
	public const LIST_LOGS = 905;

	/** Users with this permission can create payments */
	public const CREATE_PAYMENT = 1001;

	/** Users with this permission can read payments */
	public const READ_PAYMENT = 1002;

	/** Users with this permission can modify payments */
	public const MODIFY_PAYMENT = 1003;

	/** Users with this permission can delete payments */
	public const DELETE_PAYMENT = 1004;

	/** Users with this permission can list payments */
	public const LIST_PAYMENTS = 1005;

	/** Users with this permission can list their payments */
	public const LIST_OWN_PAYMENTS = 1006;

	/** Users with this permission can create roles */
	public const CREATE_ROLE = 1101;

	/** Users with this permission can read roles */
	public const READ_ROLE = 1102;

	/** Users with this permission can modify roles */
	public const MODIFY_ROLE = 1103;

	/** Users with this permission can delete roles */
	public const DELETE_ROLE = 1104;

	/** Users with this permission can list roles */
	public const LIST_ROLES = 1105;

	/** Users with this permission can view the roles page */
	public const VIEW_ROLES = 1107;

	/** Users with this permission can create users */
	public const CREATE_USER = 1201;

	/** Users with this permission can read users */
	public const READ_USER = 1202;

	/** Users with this permission can modify users */
	public const MODIFY_USER = 1203;

	/** Users with this permission can delete users */
	public const DELETE_USER = 1204;

	/** Users with this permission can list users */
	public const LIST_USERS = 1205;

	/** Users with this permission can read their own user record */
	public const READ_OWN_USER = 1206;

	/**
	 * Determine if the permission is valid
	 *
	 * @param int permission - the permission to check
	 * @return bool - true iff the permission is valid
	 */
	public static function is_valid(int $permission): bool {
		$valid_values = [
			self::CREATE_API_KEY,
			self::READ_API_KEY,
			self::MODIFY_API_KEY,
			self::DELETE_API_KEY,
			self::LIST_API_KEYS,
			self::CREATE_EQUIPMENT_AUTHORIZATION,
			self::DELETE_EQUIPMENT_AUTHORIZATION,
			self::LIST_EQUIPMENT_AUTHORIZATIONS,
			self::LIST_OWN_EQUIPMENT_AUTHORIZATIONS,
			self::LIST_CARD_TYPES,
			self::CREATE_CARD,
			self::READ_CARD,
			self::MODIFY_CARD,
			self::LIST_CARDS,
			self::LIST_OWN_CARDS,
			self::CREATE_CHARGE_POLICY,
			self::READ_CHARGE_POLICY,
			self::MODIFY_CHARGE_POLICY,
			self::DELETE_CHARGE_POLICY,
			self::LIST_CHARGE_POLICIES,
			self::CREATE_CHARGE,
			self::READ_CHARGE,
			self::MODIFY_CHARGE,
			self::DELETE_CHARGE,
			self::LIST_CHARGES,
			self::LIST_OWN_CHARGES,
			self::CREATE_EQUIPMENT_TYPE,
			self::READ_EQUIPMENT_TYPE,
			self::MODIFY_EQUIPMENT_TYPE,
			self::DELETE_EQUIPMENT_TYPE,
			self::LIST_EQUIPMENT_TYPES,
			self::CREATE_EQUIPMENT,
			self::READ_EQUIPMENT,
			self::MODIFY_EQUIPMENT,
			self::DELETE_EQUIPMENT,
			self::LIST_EQUIPMENT,
			self::CREATE_LOCATION,
			self::READ_LOCATION,
			self::MODIFY_LOCATION,
			self::DELETE_LOCATION,
			self::LIST_LOCATIONS,
			self::READ_LOG,
			self::LIST_LOGS,
			self::CREATE_PAYMENT,
			self::READ_PAYMENT,
			self::MODIFY_PAYMENT,
			self::DELETE_PAYMENT,
			self::LIST_PAYMENTS,
			self::LIST_OWN_PAYMENTS,
			self::CREATE_ROLE,
			self::READ_ROLE,
			self::MODIFY_ROLE,
			self::DELETE_ROLE,
			self::LIST_ROLES,
			self::VIEW_ROLES,
			self::CREATE_USER,
			self::READ_USER,
			self::MODIFY_USER,
			self::DELETE_USER,
			self::LIST_USERS,
			self::READ_OWN_USER,
		];
		if(in_array($permission, $valid_values)) {
			return true;
		}

		return false;
	}
}
