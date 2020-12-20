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
	 * @return self
	 */
	public function set_email(string $email) : self {
		$this->email = $email;
		return $this;
	}

}