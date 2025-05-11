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
// export const DELETE_CARD = 304;

/** Users with this permission can list equipment access cards */
export const LIST_CARDS = 305;

/** Users with this permission can list their own equipment access cards */
export const LIST_OWN_CARDS = 306;

/**
 * Users with this permission can create charge policies
 * 
 * Charge policies play a special role in the system as designed. Stored
 * Procedures in the database make decisions based on charge policy thus
 * policies are implemented as constants in code.
 */
// export const CREATE_CHARGE_POLICY = 401;

/**
 * Users with this permission can read charge policies
 * 
 * Charge policies play a special role in the system as designed. Stored
 * Procedures in the database make decisions based on charge policy thus
 * policies are implemented as constants in code.
 */
// export const READ_CHARGE_POLICY = 402;

/**
 * Users with this permission can modify charge policies
 * 
 * Charge policies play a special role in the system as designed. Stored
 * Procedures in the database make decisions based on charge policy thus
 * policies are implemented as constants in code.
 */
// export const MODIFY_CHARGE_POLICY = 403;

/**
 * Users with this permission can delete charge policies
 * 
 * Charge policies play a special role in the system as designed. Stored
 * Procedures in the database make decisions based on charge policy thus
 * policies are implemented as constants in code.
 */
// export const DELETE_CHARGE_POLICY = 404;

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

/** Users with this permission can view the roles page */
export const VIEW_ROLES = 1107;

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

let permissions = {
	1:{id:CREATE_API_KEY, name:"Add API key to system"},
	2:{id:READ_API_KEY, name:"View API key details"},
	3:{id:MODIFY_API_KEY, name:"Modify API keys"},
	4:{id:DELETE_API_KEY, name:"Delete API keys"},
	5:{id:LIST_API_KEYS, name:"List API keys"},
	101:{id:CREATE_EQUIPMENT_AUTHORIZATION, name:"Add equipment authorization to system"},
	// 102:{id:READ_EQUIPMENT_AUTHORIZATION, name:""},
	// 103:{id:MODIFY_AUTHORIZATIONS, name:"Modify the list of equipment for which a user is authorized"},
	104:{id:DELETE_EQUIPMENT_AUTHORIZATION, name:"Delete equipment authorizations"},
	105:{id:LIST_EQUIPMENT_AUTHORIZATIONS, name:"List equipment authorizations"},
	106:{id:LIST_OWN_EQUIPMENT_AUTHORIZATIONS, name:"List user's own equipment authorizations"},
	// 201:{id:CREATE_CARD_TYPE, name:"Add card types to system"},
	// 202:{id:READ_CARD_TYPE, name:"View card type details"},
	// 203:{id:MODIFY_CARD_TYPE, name:"Modify card types"},
	// 204:{id:DELETE_CARD_TYPE, name:"Delete card types"},
	205:{id:LIST_CARD_TYPES, name:"List card types"},
	301:{id:CREATE_CARD, name:"Add cards to system"},
	302:{id:READ_CARD, name:"View card details"},
	303:{id:MODIFY_CARD, name:"Modify cards"},
	// 304:{id:DELETE_CARD, name:"Delete cards"}, // Not currently possible to preserve log integrity
	305:{id:LIST_CARDS, name:"List cards"},
	306:{id:LIST_OWN_CARDS, name:"List user's own cards"},
	// 401:{id:CREATE_CHARGE_POLICY, name:"Add charge policy to system"},
	// 402:{id:READ_CHARGE_POLICY, name:"View charge policy details"},
	// 403:{id:MODIFY_CHARGE_POLICY, name:"Modify charge policies"},
	// 404:{id:DELETE_CHARGE_POLICY, name:"Delete charge policies"},
	405:{id:LIST_CHARGE_POLICIES, name:"List charge policies"},
	501:{id:CREATE_CHARGE, name:"Add charge to system"},
	502:{id:READ_CHARGE, name:"View charge details"},
	503:{id:MODIFY_CHARGE, name:"Modify charges"},
	504:{id:DELETE_CHARGE, name:"Delete charges"},
	505:{id:LIST_CHARGES, name:"List charges"},
	506:{id:LIST_OWN_CHARGES, name:"List user's own charges"},
	601:{id:CREATE_EQUIPMENT_TYPE, name:"Add equipment types to system"},
	602:{id:READ_EQUIPMENT_TYPE, name:"View equipment type details"},
	603:{id:MODIFY_EQUIPMENT_TYPE, name:"Modify equipment types"},
	604:{id:DELETE_EQUIPMENT_TYPE, name:"Delete equipment types"},
	605:{id:LIST_EQUIPMENT_TYPES, name:"List equipment types"},
	701:{id:CREATE_EQUIPMENT, name:"Add equipment to system"},
	702:{id:READ_EQUIPMENT, name:"View equipment details"},
	703:{id:MODIFY_EQUIPMENT, name:"Modify equipment"},
	704:{id:DELETE_EQUIPMENT, name:"Delete equipment"},
	705:{id:LIST_EQUIPMENT, name:"List equipment"},
	801:{id:CREATE_LOCATION, name:"Add locations to system"},
	802:{id:READ_LOCATION, name:"View location details"},
	803:{id:MODIFY_LOCATION, name:"Modify locations"},
	804:{id:DELETE_LOCATION, name:"Delete locations"},
	805:{id:LIST_LOCATIONS, name:"List locations"},
	// 901:{id:CREATE_LOG, name:"Add log entries to system"}, // Not currently used
	902:{id:READ_LOG, name:"View log entry details"},
	// MODIFY_LOG: "Modifying a Log Entry makes no sense",
	// DELETE_LOG: "Deleting a Log Entry makes no sense",
	905:{id:LIST_LOGS, name:"List log entries"},
	1001:{id:CREATE_PAYMENT, name:"Record payments in system"},
	1002:{id:READ_PAYMENT, name:"View payment details"},
	1003:{id:MODIFY_PAYMENT, name:"Modify payments"},
	1004:{id:DELETE_PAYMENT, name:"Delete payments"},
	1005:{id:LIST_PAYMENTS, name:"List payments"},
	1006:{id:LIST_OWN_PAYMENTS, name:"List user's own payments"},
	1101:{id:CREATE_ROLE, name:"Add roles to system"},
	1102:{id:READ_ROLE, name:"View role details"},
	1103:{id:MODIFY_ROLE, name:"Modify roles"},
	1104:{id:DELETE_ROLE, name:"Delete roles"},
	1105:{id:LIST_ROLES, name:"List roles"},
	1107:{id:VIEW_ROLES, name:"View roles"},
	1201:{id:CREATE_USER, name:"Add users to system"},
	1202:{id:READ_USER, name:"View user details"},
	1203:{id:MODIFY_USER, name:"Modify users"},
	1204:{id:DELETE_USER, name:"Delete users"},
	1205:{id:LIST_USERS, name:"List users"},
	1206:{id:READ_OWN_USER, name:"View user's own profile"}
};

/**  */
export function list() { return Promise.resolve(Object.values(permissions)); };

/**
 * Get the string version of a permission
 * 
 * @param {int} permission - the unique id of the permission
 * @return {String} - human readable text describing the permission
 * @throws {String} when permission is not the unique id of a permission
 */
export function name_for_permission(permission) {
	if(permission in permissions) {
		return permissions[permission].name;
	} else {
		throw "Invalid Permission";
	}
};