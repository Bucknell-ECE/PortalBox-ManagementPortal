<?php

namespace Portalbox\Entity;

/**
 * APIKey represents a token that can be used to authenticate to the REST API
 * without establishing a Session
 * 
 * @package Portalbox\Entity
 */
class APIKey extends AbstractEntity {

	/**
	 * The name of this API key
	 *
	 * @var string
	 */
	protected $name;

	/**
	 * The token that can be presented to authenticate to the API in the
	 * absence of a Session
	 *
	 * @var string
	 */
	protected $token;

	/**
	 * Get the name of this API key
	 *
	 * @return string - the name of the API key
	 */
	public function name() : string {
		return $this->name;
	}

	/**
	 * Set the name of this API key
	 *
	 * @param string name - the name for this API key
	 * @return APIKey - returns this in order to support fluent syntax.
	 */
	public function set_name(string $name) : APIKey {
		$this->name = $name;
		return $this;
	}

	/**
	 * Get token that can be presented to authenticate to the API in the
	 * absence of a Session
	 *
	 * @return string - the token that can be presented to authenticate to
	 *           the API in the absence of a Session
	 */
	public function token() : string {
		return $this->token;
	}

	/**
	 * Set token that can be presented to authenticate to the API in the
	 * absence of a Session
	 *
	 * @param string token - the token that can be presented to authenticate
	 *           to the API in the absence of a Session
	 * @return APIKey - returns this in order to support fluent syntax.
	 */
	public function set_token(string $token) : APIKey {
		$this->token = $token;
		return $this;
	}

}
