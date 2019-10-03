<?php
	/**
	 *	DB is a singelton wrapper around PDO that injects settings from the
	 *	config.ini file. A .htaccess should be used to protect the directory
	 *	containing the config.ini file.
	 *
	 *  Code adapted from Base Web Application core by T.K.Egan
	 */
	class DB extends PDO {
		private static $pdo;	/** The singelton instance */
		
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
		public function __construct($file = '../config/config.ini') {
			$settings = parse_ini_file($file, TRUE);
			if($settings != FALSE && array_key_exists('database', $settings)) {
				// should throw exception if host, database or user keys are missing
				// should support no password if key is not provided
				$dsn = 'host=' . $settings['database']['host'] . ';dbname=' . $settings['database']['database'];
				parent::__construct($settings['database']['driver'] . ':' . $dsn, $settings['database']['username'], $settings['database']['password']);
			} else {
				// throw exception
				die('Unable to read settings from config file');
			}
		}
		
		/**
		 *	getConnection - accessor to the PDO singleton
		 *
		 *	@return - the singleton Database connection as a PDO subclass instance
		 */
		public static function getConnection() {
			if(!self::$pdo) {
				try {
					self::$pdo = new DB();
				} catch(PDOException $e) {
					// error_log($e->getMessage());
					header('HTTP/1.0 500 Internal Server Error');
					die('We were unable to connect to the backend database. Please ask your server administrator to investigate');
				}
			}
			
			return self::$pdo;
		}
	}
?>