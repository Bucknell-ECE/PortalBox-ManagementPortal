<?php

namespace Portalbox\Session;

use Portalbox\Config;
use Portalbox\Entity\User;
use Portalbox\Model\Entity\User as PDOAwareUser;
use Portalbox\Model\APIKeyModel;
use Portalbox\Model\UserModel;
use Portalbox\Query\APIKeyQuery;

/**
 * Session by nature is a weird singleton; it is in a sense part of the request
 * but we don't really want to deal with the low level stuff, what we want is to
 * determine if there is an authenticated user and what there permissions are.
 * To solve this, we have created `Session` which hides the low level session
 * management. Simply create an instance of `Session`, and ask it for the
 * authenticated user.
 *
 * We are currently transitioning from some namespaced methods to an instance
 * that can be set as a property of "Services" allowing "Services" to make
 * authentication and authorization decisions in a manner that lends itself to
 * automated testing.
 */
class Session {
	public const ERROR_NOT_AUTHENTICATED = 'Your session is invalid. Perhaps you need to reauthenticate.';
	public const ERROR_NOT_AUTHORIZED = 'You have not been granted access to this information.';

	/**
	 * The authenticated user
	 * @var User|null
	 */
	private $authenticated_user = null;

	/**
	 * Get the currently authenticated User
	 *
	 * @return User|null - the currently authenticated user or null if there
	 *     is not a currently authenticated user
	 */
	public function get_authenticated_user(): ?User {
		if (!$this->authenticated_user) {
			$config = Config::config();

			// Check for Brearer token
			if (
				array_key_exists('HTTP_AUTHORIZATION', $_SERVER) &&
				8 < strlen($_SERVER['HTTP_AUTHORIZATION']) &&
				0 == strcmp('Bearer ', substr($_SERVER['HTTP_AUTHORIZATION'], 0, 7))
			) {
				$token = substr($_SERVER['HTTP_AUTHORIZATION'], 7);


				$model = new APIKeyModel($config);

				$query = (new APIKeyQuery())->set_token($token);

				$keys = $model->search($query);

				if ($keys && 0 < count($keys)) {
					// get key 0 and construct a fake user for it.
					$this->authenticated_user = (new PDOAwareUser($config))
						->set_name($keys[0]->name())
						->set_is_active(true)
						->set_role_id(3);	// API key act as admins...
											// in future should add a role_id field
											// to keys and restrict them accordingly

					return $this->authenticated_user;
				}
			}

			// Check for cookie based session
			if (PHP_SESSION_ACTIVE !== session_status()) {
				$success = session_start();
				if (!$success) {
					session_abort();	// should shutdown execution but just in case...
					http_response_code(500);
					die('The operating evnvironment is improperly configured for tracking user sessions. Please notify the administrator');
				}
			}

			if (array_key_exists('user_id', $_SESSION)) {
				$model = new UserModel($config);
				$this->authenticated_user = $model->read($_SESSION['user_id']);
			} else {
				return null;
			}
		}

		return $this->authenticated_user;
	}

	/**
	 * A convenience method that returns an HTTP 403 response if there is not
	 * an authenticated user.
	 */
	public function require_authentication(): void {
		if (null === $this->get_authenticated_user()) {
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
	public function check_authorization(int $permission): bool {
		$this->require_authentication();

		return $this->get_authenticated_user()->role()->has_permission($permission);
	}

	/**
	 * A convenience method that returns an HTTP 403 response if the user is
	 * not authorized.
	 */
	public function require_authorization(int $permission): void {
		if (!$this->check_authorization($permission)) {
			http_response_code(403);
			die('You have not been granted access to this information.');
		}
	}
}
