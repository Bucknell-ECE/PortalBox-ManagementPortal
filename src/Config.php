<?php

namespace Bucknell\Portalbox;

use PDO;

/**
 * Application Configuration by nature is a weird singleton. There can be
 * only the one configuration and there is a significant penalty to reading
 * the configuration so we'll make the configuration a singleton
 */
class Config {
	/** The singelton instance */
	private static $instance;

	/** Cached configuration data */
	private $settings;
	
	/**
	 *	__construct - reads the specified config file or if one is not specified
		*		looks for a file named 'config.ini' in the include path. Using the
		*		settings in the config file it then creates a PDO database instance
		*
		*	@param file - the name of the config file to read. defaults to
		*		'core/config.ini'
		*
		*	@sideeffect - if the config file can not be found and read, script
		*		execution will end abnormally
		*/
	public function __construct($file = 'config/config.ini') {
		$this->settings = parse_ini_file($file, TRUE);
	}
	
	/**
	 * config - accessor to the configuration singleton
	 *
	 * @return Config - the singleton configuration
	 */
	public static function config() : Config {
		if(!self::$instance) {
			self::$instance = new Config();
		}

		return self::$instance;
	}

	/**
	 * Get a database connection using the configured connection params
	 *
	 * @throws InvalidConfigurationException if the configuration does not
	 *		contain the necessary configuration parameters
	 * @return PDO - a connection to the database
	 */
	public function connection() : PDO {
		$connection = null;

		if(FALSE != $this->settings && array_key_exists('database', $this->settings)) {
			// should throw exception if host, database or user keys are missing
			// should support no password if key is not provided
			$dsn = 'host=' . $this->settings['database']['host'] . ';dbname=' . $this->settings['database']['database'];
			return new PDO($this->settings['database']['driver'] . ':' . $dsn, $this->settings['database']['username'], $this->settings['database']['password']);
		}

		throw new InvalidConfigurationException();
	}
}