<?php

namespace Portalbox;

use Portalbox\Entity\User;
use Portalbox\Model\Entity\User as PDOAwareUser;
use Portalbox\Model\APIKeyModel;
use Portalbox\Model\UserModel;
use Portalbox\Query\APIKeyQuery;

/**
 * Session by nature is a weird singleton that manifests a part of the request
 * handler that exists external to the handler like environment variables.
 * There can be only the one session per request handler.
 */
class Session {
	/**
	 * The authenticated user
	 * @var User
	 */
	private static $authenticated_user;

	/**
	 * Get the currently authenticated User
	 *
	 * @return User|null - the currently authenticated user or null if there
	 *     is not a currently authenticated user
	 */
	public static function get_authenticated_user() : ?User {
		if(!self::$authenticated_user) {
			
			$config = Config::config();
			
			// Check for Brearer token
			if(array_key_exists('HTTP_AUTHORIZATION', $_SERVER) &&
				8 < strlen($_SERVER['HTTP_AUTHORIZATION']) &&
				0 == strcmp('Bearer ', substr($_SERVER['HTTP_AUTHORIZATION'], 0 , 7))) {
				$token = substr($_SERVER['HTTP_AUTHORIZATION'], 7);
				

				$model = new APIKeyModel($config);
				
				$query = (new APIKeyQuery)->set_token($token);
				
				$keys = $model->search($query);
				
				if($keys && 0 < count($keys)) {
					// get key 0 and construct a fake user for it.
					self::$authenticated_user = (new PDOAwareUser($config))
						->set_name($keys[0]->name())
						->set_is_active(true)
						->set_role_id(3);	// API key act as admins... 
											// in future should add a role_id field
											// to keys and restrict them accordingly

					return self::$authenticated_user;
				}
			}
			
			// Check for cookie based session 
			if(PHP_SESSION_ACTIVE !== session_status()) {
				$success = session_start();
				if(!$success) {
					session_abort();	// should shutdown execution but just in case...
					http_response_code(500);
					die('The operating evnvironment is improperly configured for tracking user sessions. Please notify the administrator');
				}
			}

			if(array_key_exists('user_id', $_SESSION)) {
				$model = new UserModel($config);
				self::$authenticated_user = $model->read($_SESSION['user_id']);
			} else {
				return NULL;
			}
		}

		return self::$authenticated_user;
	}

	/**
	 * A convenience method that returns an HTTP 403 response if there is not
	 * an authenticated user.
	 */
	public static function require_authentication() {
		if(NULL === self::get_authenticated_user()) {
			http_response_code(403);
			die('Your session is invalid. Perhaps you need to reauthenticate.');
		}
	}

	/**
	 * A convenience method that returns true iff the user is authenticated and
	 * has the specified permission. An HTTP 403 response will be sent and
	 * script execution terminated if the user is not authenticated.
	 * 
	 * @param int permission the Permission for which to check. Must be one of
	 *     the constants exposed in Portalbox\Entity\Permission to result in
	 *     true being returned
	 * @return bool true iff the User is authenticated and has the specified
	 *     permission
	 */
	public static function check_authorization(int $permission) : bool {
		self::require_authentication();

		return self::get_authenticated_user()->role()->has_permission($permission);
	}

	/**
	 * A convenience method that returns an HTTP 403 response if the user is
	 * not authorized.
	 */
	public static function require_authorization(int $permission) {
		if(!self::check_authorization($permission)) {
			http_response_code(403);
			die('You have not been granted access to this information.');
		}
	}
}