<?php
	class SecurityContext {
		private static $context;	/** The singelton instance */

		public $authorization_level;
		public $authorized_user_id;

		private function __construct() {
			$this->authorization_level = -1;
			$this->authorized_user_id = -1;
		}

		public static function getContext() {
			if(!self::$context) {
				self::$context = new SecurityContext();
			}
			
			return self::$context;
		}
	}

	// Should enumerate authentication levels???

	/**
	 * Determine if the HTTP request being serviced is associated with an
	 * authenticated user.
	 * 
	 * @return (bool) - true if an authenticated user is associated with the
	 * 		HTTP request being serviced
	 */
	function is_user_authenticated() {
		if(-1 < SecurityContext::getContext()->authorization_level) {
			return true;
		}

		// Check for Brearer token
		if(array_key_exists('HTTP_AUTHORIZATION', $_SERVER) &&
			8 < strlen($_SERVER['HTTP_AUTHORIZATION']) &&
			0 == strcmp('Bearer ', substr($_SERVER['HTTP_AUTHORIZATION'], 0 , 7))) {
			$token = substr($_SERVER['HTTP_AUTHORIZATION'], 7);

			if((include_once '../lib/Database.php') === FALSE) {
				header('HTTP/1.0 500 Internal Server Error');
				die('We were unable to load some dependencies. Please ask your server administrator to investigate');
			}

			$connection = DB::getConnection();
			$sql = 'SELECT id FROM api_keys WHERE token = :token';
			$query = $connection->prepare($sql);
			$query->bindValue(':token', $token);
			if($query->execute()) {
				if($row = $query->fetch(\PDO::FETCH_NUM)) {
					// authorization level is ???
					// full access now but maybe should be restricted
					// by all key or for specific keys in future?
					SecurityContext::getContext()->authorization_level = 3;
					return true;
				}
			}
		}

		// Check for cookie based session 
		if(session_status() !== PHP_SESSION_ACTIVE) {
			$success = session_start();
			if(!$success) {
				session_abort();
				return false;	// just in case session_abort fails to shutdown thread
			}
		}

		if(array_key_exists('user', $_SESSION)) {
			SecurityContext::getContext()->authorization_level = $_SESSION['user']['management_portal_access_level_id'];
			SecurityContext::getContext()->authorized_user_id = $_SESSION['user']['id'];
			return true;
		}

		return false;
	}

	/**
	 * Determine the authorization level of the authenticated user associacted
	 * with the HTTP request beng serviced.
	 * 
	 * @return (int) - one of the enumerated values:
	 * 		3 - if the user is an admin
	 * 		2 - if the user is a trainer
	 * 		1 - if the user has no priveleges beyond their own data
	 * 		0 - if there is no authenticated user
	 */
	function get_user_authorization_level() {
		if(is_user_authenticated()) {
			return SecurityContext::getContext()->authorization_level;
		} else {
			// sure context initializes to 0 but if is_user_authenticated is
			// not called first, an authenticated user will have the incorrect
			// level 0 the if structure helps those who are speed reading and
			// ignoring long comments
			return 0;
		}
	}

	/**
	 * Terminate the servicing of the HTTP request unless the request is
	 * associated with an authenticated user
	 */
	function require_authentication() {
		if(!is_user_authenticated()) {
			header('HTTP/1.0 403 Not Authorized');
			die('Your session is invalid. Perhaps you need to reauthenticate.');
		}
	}

	/**
	 * Terminate the seriving of the HTTP request unless the request is
	 * associated with an authenticated user of at least the requested
	 * level
	 * 
	 * @param (string) authorization_level - the minimum authorization level
	 * 		which the authenticated user must have in order to continue
	 * 		servicing the request
	 */
	function require_authorization($authorization_level) {
		require_authentication();

		$level = SecurityContext::getContext()->authorization_level;

		// WARNING hard coded values
		switch($authorization_level) {
			case 'admin':	//only admins can use this endpoint
				if(3 != $level) {
					header('HTTP/1.0 403 Not Authorized');
					die('You have not been granted privileges for this data.');
				}
			case 'trainer':
				if(2 > $level) {
					header('HTTP/1.0 403 Not Authorized');
					die('You have not been granted privileges for this data.');
				}
		}
	}
?>