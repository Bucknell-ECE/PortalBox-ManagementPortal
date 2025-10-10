<?php

namespace Portalbox\Query;

/**
 * APIKeyQuery Presents a standard interface for APIKey search queries
 */
class APIKeyQuery {
	/**
	 * The token for which to search.
	 */
	protected ?string $token = null;

	/**
	 * Get the token for which to search
	 */
	public function token(): ?string {
		return $this->token;
	}

	/**
	 * Set the token for which to search
	 */
	public function set_token(?string $token): self {
		$this->token = $token;
		return $this;
	}
}
