<?php

namespace Portalbox\Query;

/**
 * UserQuery presents a standard interface for User search queries
 * 
 * @package Portalbox\Query
 */
class UserQuery {
	/**
	 * The email address of the user for which to search
	 *
	 * @var string
	 */
	protected $email;

	/**
	 * Get the email address of the user for which to search
	 *
	 * @return string|null - the email address of the user for which to search
	 */
	public function email() : ?string {
		return $this->email;
	}

	/**
	 * Set the email address of the user for which to search
	 *
	 * @param string email - the email address of the user for which to search
	 * @return UserQuery - returns this in order to support fluent syntax.
	 */
	public function set_email(string $email) : UserQuery {
		$this->email = $email;
		return $this;
	}

}