<?php
	// Should enumerate authentication levels???

	/**
	 * Determine if the HTTP request being serviced is associated with an
	 * authenticated user.
	 * 
	 * @return (bool) - true if an authenticated user is associated with the
	 * 		HTTP request being serviced
	 */
	function is_user_authenticated() {
		if(session_status() !== PHP_SESSION_ACTIVE) {
			$success = session_start();
			if(!$success) {
				session_abort();
			} else {
				if(array_key_exists('user', $_SESSION)) {
					return true;
				}
			}
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
	 * 		1 - if the user has no priveleges
	 * 		0 - if there is no authenticated user
	 */
	function get_user_authorization_level() {
		if(is_user_authenticated()) {
			return $_SESSION['user']['management_portal_access_level_id'];
		} else {
			return 0;
		}
	}

	/**
	 * Terminate the servicing of the HTTP request unless the request is
	 * associated with an authenticated user
	 */
	function require_authentication() {
		if(session_status() !== PHP_SESSION_ACTIVE) {
			$success = session_start();
			if(!$success) {
				session_abort();
				header('HTTP/1.0 403 Not Authorized');
				die('You must request a session cookie from the api using the login.php endpoint before accessing this endpoint');
			}
		}
		if(!array_key_exists('user', $_SESSION)) {
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

		// WARNING hard coded values
		switch($authorization_level) {
			case 'admin':	//only admins can use this endpoint
				if(3 != $_SESSION['user']['management_portal_access_level_id']) {
					header('HTTP/1.0 403 Not Authorized');
					die('You have not been granted privileges for this data.');
				}
			case 'trainer':
				if(2 > $_SESSION['user']['management_portal_access_level_id']) {
					header('HTTP/1.0 403 Not Authorized');
					die('You have not been granted privileges for this data.');
				}
		}
	}
?>