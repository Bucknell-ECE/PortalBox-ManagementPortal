<?php

namespace Portalbox\Query;

/**
 * APIKeyQuery Presents a standard interface for APIKey search queries
 * 
 * @package Portalbox\Query
 */
class APIKeyQuery {
	/**
	 * The token for which to search.
	 *
	 * @var string
	 */
	protected $token;

	/**
	 * Get the token for which to search
	 *
	 * @return string|null - the token for which to search
	 */
	public function token() : ?string {
		
		return $this->token;
	}

	/**
	 * Set the token for which to search
	 *
	 * @param string token - the token for which to search
	 * @return self
	 */
	public function set_token(string $token) : self {
		
		$this->token = $token;
		return $this;
	}

}