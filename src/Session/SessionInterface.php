<?php

namespace Portalbox\Session;

use Portalbox\Entity\User;

interface SessionInterface {
	/**
	 * Get the currently authenticated User
	 *
	 * @return User|null - the currently authenticated user or null if there
	 *     is not a currently authenticated user
	 */
	public function get_authenticated_user(): ?User;
}
