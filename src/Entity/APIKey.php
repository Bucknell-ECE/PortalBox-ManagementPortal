<?php

namespace Portalbox\Entity;

use InvalidArgumentException;

/**
 * APIKey represents a token that can be used to authenticate to the REST API
 * without establishing a User Session
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
	 * absence of a User Session
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
	 * @return self
	 */
	public function set_name(string $name) : self {
		if(0 < strlen($name)) {
			$this->name = $name;
			return $this;
		}

		throw new InvalidArgumentException('You must specify the API key\'s name');
	}

	/**
	 * Get token that can be presented to authenticate to the API in the
	 * absence of a Session
	 *
	 * @return string - the token that can be presented to authenticate to
	 *           the API in the absence of a Session
	 */
	public function token() : string {
		if(NULL === $this->token) {
			$this->token = $this->create_token();
		}
		return $this->token;
	}

	/**
	 * Set token that can be presented to authenticate to the API in the
	 * absence of a Session
	 *
	 * @param string token - the token that can be presented to authenticate
	 *           to the API in the absence of a Session
	 * @return self
	 */
	public function set_token(string $token) : self {
		$this->token = $token;
		return $this;
	}

	private function create_token() {
		// If libsodium is available use it :)
		if(true === function_exists('random_bytes')) {
			return bin2hex(random_bytes(16));
		} else {
			return sprintf('%04X%04X%04X%04X%04X%04X%04X%04X', mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535));
		}
	}
}
