<?php

namespace Portalbox;

use PDO;
use Portalbox\Exception\InvalidConfigurationException;

/**
 * Application Configuration by nature is a weird singleton. There can be
 * only the one configuration and there is a penalty to reading the
 * configuration so we make the configuration a singleton
 */
class Config {
	/** The singelton instance */
	private static $instance;

	/** Cached configuration data */
	private $settings;

	/** Cached DB connection */
	private $connection;
	
	/**
	 * __construct - reads the specified config file or if one is not specified
	 *     looks for a file named 'config.ini' in the include path. Using the
	 *     settings in the config file it then creates a PDO database instance
	 *
	 * @param file - the name of the config file to read. Defaults to
	 *     '../config/config.ini' ie a fle named 'config.ini' in a directory
	 *     named 'config' in thesame directory as src.
	 *
	 * @sideeffect - if the config file can not be found and read, script
	 *     execution will end abnormally
	 */
	public function __construct($file = '../config/config.ini') {
		$path = realpath(__DIR__ . DIRECTORY_SEPARATOR . $file);
		$this->settings = parse_ini_file($path, TRUE);
	}
	
	/**
	 * config - accessor to the configuration singleton
	 *
	 * @param file - the name of the config file to read. Defaults to
	 *     '../config/config.ini'
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
	private function connection() : PDO {
		$connection = null;

		if(FALSE != $this->settings && array_key_exists('database', $this->settings)) {
			// should throw exception if host, database or user keys are missing
			// should support no password if key is not provided
			$dsn = 'host=' . $this->settings['database']['host'] . ';dbname=' . $this->settings['database']['database'];
			return new PDO($this->settings['database']['driver'] . ':' . $dsn, $this->settings['database']['username'], $this->settings['database']['password']);
		}

		throw new InvalidConfigurationException();
	}

	/**
	 * Get a database connection using the configured connection params
	 * that can write (INSERT, UPDATE, DELETE) to the db
	 * 
	 * In a scaled out deployment it may be necessary to have replication
	 * slaves take some of the load. They can easily take read load without
	 * a complicated replication setup. By using this method to get a writable
	 * connection only when necessary we position ourselves to implement such a
	 * scale out in the future if needed.
	 *
	 * @throws InvalidConfigurationException if the configuration does not
	 *		contain the necessary configuration parameters
	 * @return PDO - a connection to the database
	 */
	public function writable_db_connection() : PDO {
		return $this->connection();
	}

	/**
	 * Get a database connection using the configured connection params
	 * that can only read from the db
	 * 
	 * In a scaled out deployment it may be necessary to have replication
	 * slaves take some of the load. They can easily take read load without
	 * a complicated replication setup. By using this method to get a read only
	 * connection whenever possible we position ourselves to implement such a
	 * scale out in the future if needed.
	 *
	 * @throws InvalidConfigurationException if the configuration does not
	 *		contain the necessary configuration parameters
	 * @return PDO - a connection to the database
	 */
	public function readonly_db_connection() : PDO {
		return $this->connection();
	}

	/**
	 * Get the settings for the web ui
	 *
	 */
	public function web_ui_settings() : array {
		if(FALSE != $this->settings && array_key_exists('oauth', $this->settings)) {
			return $this->settings['oauth'];
		}

		// should toss exception?
	}
}