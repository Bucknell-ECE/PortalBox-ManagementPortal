<?php

namespace Portalbox\Enumeration;

/**
 * Permission represents a permission which a particular role may have
 */
enum Permission: int {
	/** Users with this permission can create API keys */
	case CREATE_API_KEY = 1;

	/** Users with this permission can read API keys */
	case READ_API_KEY = 2;

	/** Users with this permission can modify API keys */
	case MODIFY_API_KEY = 3;

	/** Users with this permission can delete API keys */
	case DELETE_API_KEY = 4;

	/** Users with this permission can list API keys */
	case LIST_API_KEYS = 5;

	/** Users with this permission can create badge rules */
	case CREATE_BADGE_RULE = 51;

	/** Users with this permission can read badge rules */
	case READ_BADGE_RULE = 52;

	/** Users with this permission can modify badge rules */
	case MODIFY_BADGE_RULE = 53;

	/** Users with this permission can delete badge rules*/
	case DELETE_BADGE_RULE = 54;

	/** Users with this permission can list badge rules */
	case LIST_BADGE_RULES = 55;

	/** Users with this permission can generate badge reports */
	case REPORT_BADGES = 57;

	/** Users with this permission can create equipment authorizations */
	case CREATE_EQUIPMENT_AUTHORIZATION = 101;

	/** Users with this permission can delete equipment authorizations */
	case DELETE_EQUIPMENT_AUTHORIZATION = 104;

	/** Users with this permission can list equipment authorizations */
	case LIST_EQUIPMENT_AUTHORIZATIONS = 105;

	/** Users with this permission can list their equipment authorizations */
	case LIST_OWN_EQUIPMENT_AUTHORIZATIONS = 106;

	/**
	 * Users with this permission can list card types
	 */
	case LIST_CARD_TYPES = 205;

	/** Users with this permission can create equipment access cards */
	case CREATE_CARD = 301;

	/** Users with this permission can read equipment access cards */
	case READ_CARD = 302;

	/** Users with this permission can modify equipment access cards */
	case MODIFY_CARD = 303;

	/** Users with this permission can list equipment access cards */
	case LIST_CARDS = 305;

	/** Users with this permission can list their own equipment access cards */
	case LIST_OWN_CARDS = 306;

	/** Users with this permission can create charge policies
	 *
	 * Charge policies play a special role in the system as designed. Stored
	 * Procedures in the database make decisions based on charge policy thus
	 * policies are implemented as constants in code.
	 */
	case CREATE_CHARGE_POLICY = 401;

	/** Users with this permission can read charge policies
	 *
	 * Charge policies play a special role in the system as designed. Stored
	 * Procedures in the database make decisions based on charge policy thus
	 * policies are implemented as constants in code.
	 */
	case READ_CHARGE_POLICY = 402;

	/** Users with this permission can modify charge policies
	 *
	 * Charge policies play a special role in the system as designed. Stored
	 * Procedures in the database make decisions based on charge policy thus
	 * policies are implemented as constants in code.
	 */
	case MODIFY_CHARGE_POLICY = 403;

	/** Users with this permission can delete charge policies
	 *
	 * Charge policies play a special role in the system as designed. Stored
	 * Procedures in the database make decisions based on charge policy thus
	 * policies are implemented as constants in code.
	 */
	case DELETE_CHARGE_POLICY = 404;

	/** Users with this permission can list charge policies */
	case LIST_CHARGE_POLICIES = 405;

	/** Users with this permission can create charges */
	case CREATE_CHARGE = 501;

	/** Users with this permission can read charges */
	case READ_CHARGE = 502;

	/** Users with this permission can modify charges */
	case MODIFY_CHARGE = 503;

	/** Users with this permission can delete charges */
	case DELETE_CHARGE = 504;

	/** Users with this permission can list charges */
	case LIST_CHARGES = 505;

	/** Users with this permission can list their own charges */
	case LIST_OWN_CHARGES = 506;

	/** Users with this permission can create equipment types */
	case CREATE_EQUIPMENT_TYPE = 601;

	/** Users with this permission can read equipment types */
	case READ_EQUIPMENT_TYPE = 602;

	/** Users with this permission can modify equipment types */
	case MODIFY_EQUIPMENT_TYPE = 603;

	/** Users with this permission can delete equipment types */
	case DELETE_EQUIPMENT_TYPE = 604;

	/** Users with this permission can list equipment types */
	case LIST_EQUIPMENT_TYPES = 605;

	/** Users with this permission can create equipment */
	case CREATE_EQUIPMENT = 701;

	/** Users with this permission can read equipment */
	case READ_EQUIPMENT = 702;

	/** Users with this permission can modify equipment */
	case MODIFY_EQUIPMENT = 703;

	/** Users with this permission can delete equipment */
	case DELETE_EQUIPMENT = 704;

	/** Users with this permission can list equipment */
	case LIST_EQUIPMENT = 705;

	/** Users with this permission can create locations */
	case CREATE_LOCATION = 801;

	/** Users with this permission can read locations */
	case READ_LOCATION = 802;

	/** Users with this permission can modify locations */
	case MODIFY_LOCATION = 803;

	/** Users with this permission can delete locations */
	case DELETE_LOCATION = 804;

	/** Users with this permission can list locations */
	case LIST_LOCATIONS = 805;

	/** Users with this permission can read log entries */
	case READ_LOG = 902;

	/** Users with this permission can list log entries */
	case LIST_LOGS = 905;

	/** Users with this permission can create payments */
	case CREATE_PAYMENT = 1001;

	/** Users with this permission can read payments */
	case READ_PAYMENT = 1002;

	/** Users with this permission can modify payments */
	case MODIFY_PAYMENT = 1003;

	/** Users with this permission can delete payments */
	case DELETE_PAYMENT = 1004;

	/** Users with this permission can list payments */
	case LIST_PAYMENTS = 1005;

	/** Users with this permission can list their payments */
	case LIST_OWN_PAYMENTS = 1006;

	/** Users with this permission can create roles */
	case CREATE_ROLE = 1101;

	/** Users with this permission can read roles */
	case READ_ROLE = 1102;

	/** Users with this permission can modify roles */
	case MODIFY_ROLE = 1103;

	/** Users with this permission can delete roles */
	case DELETE_ROLE = 1104;

	/** Users with this permission can list roles */
	case LIST_ROLES = 1105;

	/** Users with this permission can create users */
	case CREATE_USER = 1201;

	/** Users with this permission can read users */
	case READ_USER = 1202;

	/** Users with this permission can modify users */
	case MODIFY_USER = 1203;

	/** Users with this permission can delete users */
	case DELETE_USER = 1204;

	/** Users with this permission can list users */
	case LIST_USERS = 1205;

	/** Users with this permission can read their own user record */
	case READ_OWN_USER = 1206;
}
