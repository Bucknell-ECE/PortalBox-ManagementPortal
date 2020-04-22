<?php

namespace Portalbox\Model;

use Portalbox\Config;

/**
 * Abstract model is the common foundation on which our object models are built.
 * 
 * @package Portalbox\Model
 */
class AbstractModel {
	/**
	 * The configuration to use
	 * 
	 * @var Config
	 */
	private $configuration;

	/**
	 * @param Config configuration - the configuration to use
	 */
	public function __construct(Config $configuration) {
		$this->set_configuration($configuration);
	}

	/**
	 * Get the configuration to use
	 *
	 * @return Config - the configuration to use
	 */
	public function configuration() : Config {
		return $this->configuration;
	}

	/**
	 * Set the configuration to use
	 *
	 * @param Config configuration - the configuration to use
	 */
	public function set_configuration(Config $configuration) {
		$this->configuration = $configuration;
	}
}
