/**
 * An Enumeration of the permissions a user's role might have 
 */

/** Users with this permission can create API keys */
export const CREATE_API_KEY = 1;

/** Users with this permission can read API keys */
export const READ_API_KEY = 2;

/** Users with this permission can modify API keys */
export const MODIFY_API_KEY = 3;

/** Users with this permission can delete API keys */
export const DELETE_API_KEY = 4;

/** Users with this permission can list API keys */
export const LIST_API_KEYS = 5;

/** Users with this permission can create equipment authorizations */
export const CREATE_EQUIPMENT_AUTHORIZATION = 101;

/**
 * Users with this permission can read equipment authorizations
 * 
 * Currently unused; Listing is sufficient in the current design
 */
//export const READ_EQUIPMENT_AUTHORIZATION = 102;

/**
 * Users with this permission can modify authorizations
 * 
 * Currently unused; users have a list of authorizations... either they
 * have authorization or they don't. So creating and deleting a user's
 * authorizations are sufficient in the current model
 */
// export const MODIFY_AUTHORIZATION = 103;

/** Users with this permission can delete equipment authorizations */
export const DELETE_EQUIPMENT_AUTHORIZATION = 104;

/** Users with this permission can list equipment authorizations */
export const LIST_EQUIPMENT_AUTHORIZATIONS = 105;

/** Users with this permission can list their equipment authorizations */
export const LIST_OWN_EQUIPMENT_AUTHORIZATIONS = 106;

/**
 * Users with this permission can create card types
 *
 * Currently unused. Card types play a special role in the system as
 * designed. Code in the IoT Application makes decisions based on the
 * card type thus types are implemented as constants in the code
 */
// export const CREATE_CARD_TYPE = 201;

/**
 * Users with this permission can read card types
 *
 * Currently unused. Card types play a special role in the system as
 * designed. Code in the IoT Application makes decisions based on the
 * card type thus types are implemented as constants in the code
 */
// export const READ_CARD_TYPE = 202;

/**
 * Users with this permission can modify card types
 *
 * Currently unused. Card types play a special role in the system as
 * designed. Code in the IoT Application makes decisions based on the
 * card type thus types are implemented as constants in the code
 */
// export const MODIFY_CARD_TYPE = 203;

/**
 * Users with this permission can delete card types
 *
 * Currently unused. Card types play a special role in the system as
 * designed. Code in the IoT Application makes decisions based on the
 * card type thus types are implemented as constants in the code
 */
// export const DELETE_CARD_TYPE = 204;

/**
 * Users with this permission can list card types
 */
export const LIST_CARD_TYPES = 205;

/** Users with this permission can create equipment access cards */
export const CREATE_CARD = 301;

/** Users with this permission can read equipment access cards */
export const READ_CARD = 302;

/** Users with this permission can modify equipment access cards */
export const MODIFY_CARD = 303;

/** 
 * Users with this permission can delete equipment access cards
 *
 * Currently usused and probably never will be used as our logs have a
 * foreign key relationship to cards.
 */
//export const DELETE_CARD = 304;

/** Users with this permission can list equipment access cards */
export const LIST_CARDS = 305;

/** Users with this permission can list their own equipment access cards */
export const LIST_OWN_CARDS = 306;

/** Users with this permission can create charge policies */
export const CREATE_CHARGE_POLICY = 401;

/** Users with this permission can read charge policies */
export const READ_CHARGE_POLICY = 402;

/** Users with this permission can modify charge policies */
export const MODIFY_CHARGE_POLICY = 403;

/** Users with this permission can delete charge policies */
export const DELETE_CHARGE_POLICY = 404;

/** Users with this permission can list charge policies */
export const LIST_CHARGE_POLICIES = 405;

/** Users with this permission can create charges */
export const CREATE_CHARGE = 501;

/** Users with this permission can read charges */
export const READ_CHARGE = 502;

/** Users with this permission can modify charges */
export const MODIFY_CHARGE = 503;

/** Users with this permission can delete charges */
export const DELETE_CHARGE = 504;

/** Users with this permission can list charges */
export const LIST_CHARGES = 505;

/** Users with this permission can list their own charges */
export const LIST_OWN_CHARGES = 506;

// CONFIG for now does not have permissions

/** Users with this permission can create equipment types */
export const CREATE_EQUIPMENT_TYPE = 601;

/** Users with this permission can read equipment types */
export const READ_EQUIPMENT_TYPE = 602;

/** Users with this permission can modify equipment types */
export const MODIFY_EQUIPMENT_TYPE = 603;

/** Users with this permission can delete equipment types */
export const DELETE_EQUIPMENT_TYPE = 604;

/** Users with this permission can list equipment types */
export const LIST_EQUIPMENT_TYPES = 605;

/** Users with this permission can create equipment */
export const CREATE_EQUIPMENT = 701;

/** Users with this permission can read equipment */
export const READ_EQUIPMENT = 702;

/** Users with this permission can modify equipment */
export const MODIFY_EQUIPMENT = 703;

/** Users with this permission can delete equipment */
export const DELETE_EQUIPMENT = 704;

/** Users with this permission can list equipment */
export const LIST_EQUIPMENT = 705;

/** Users with this permission can create locations */
export const CREATE_LOCATION = 801;

/** Users with this permission can read locations */
export const READ_LOCATION = 802;

/** Users with this permission can modify locations */
export const MODIFY_LOCATION = 803;

/** Users with this permission can delete locations */
export const DELETE_LOCATION = 804;

/** Users with this permission can list locations */
export const LIST_LOCATIONS = 805;

/**
 * Users with this permission can create log entries
 *
 * Currently usused
 */
//export const CREATE_LOG = 901;

/** Users with this permission can read log entries */
export const READ_LOG = 902;

/**
 * Users with this permission can modify log entries
 *
 * Currently usused and probably never will be used as our logs are
 * designed to be append only
 */
//export const MODIFY_LOG = 903;

/**
 * Users with this permission can delete log entries
 *
 * Currently usused and probably never will be used as our logs are
 * designed to be append only
 */
//export const DELETE_LOG = 904;

/** Users with this permission can list log entries */
export const LIST_LOGS = 905;

/** Users with this permission can create payments */
export const CREATE_PAYMENT = 1001;

/** Users with this permission can read payments */
export const READ_PAYMENT = 1002;

/** Users with this permission can modify payments */
export const MODIFY_PAYMENT = 1003;

/** Users with this permission can delete payments */
export const DELETE_PAYMENT = 1004;

/** Users with this permission can list payments */
export const LIST_PAYMENTS = 1005;

/** Users with this permission can list their payments */
export const LIST_OWN_PAYMENTS = 1006;

/** Users with this permission can create roles */
export const CREATE_ROLE = 1101;

/** Users with this permission can read roles */
export const READ_ROLE = 1102;

/** Users with this permission can modify roles */
export const MODIFY_ROLE = 1103;

/** Users with this permission can delete roles */
export const DELETE_ROLE = 1104;

/** Users with this permission can list roles */
export const LIST_ROLES = 1105;

/** Users with this permission can create users */
export const CREATE_USER = 1201;

/** Users with this permission can read users */
export const READ_USER = 1202;

/** Users with this permission can modify users */
export const MODIFY_USER = 1203;

/** Users with this permission can delete users */
export const DELETE_USER = 1204;

/** Users with this permission can list users */
export const LIST_USERS = 1205;

/** Users with this permission can read their own user record */
export const READ_OWN_USER = 1206;
