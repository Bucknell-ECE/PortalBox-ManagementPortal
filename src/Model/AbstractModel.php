<?php

namespace Portalbox\Model;

use Portalbox\Config;

/**
 * Abstract model is the common foundation on which our object models are built.
 */
class AbstractModel {
	/**
	 * The configuration to use to connect to the database
	 */
	private Config $configuration;

	/**
	 * @param Config configuration - the configuration to use
	 */
	public function __construct(Config $configuration) {
		$this->set_configuration($configuration);
	}

	/**
	 * Get the configuration to use to connect to the database
	 */
	public function configuration(): Config {
		return $this->configuration;
	}

	/**
	 * Set the configuration to use to connect to the database
	 */
	public function set_configuration(Config $configuration): void {
		$this->configuration = $configuration;
	}
}
