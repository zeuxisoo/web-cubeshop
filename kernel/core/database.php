<?php
if (defined("IN_APPS") === false) exit("Access Dead");

class Database {

	private static $prefix = "";

	public static function create($config = array()) {
		if (empty($config) === true) {
			self::debug('Configure can not empty');
		}
	
		if (file_exists(KERNEL_ROOT.'/driver/'.strtolower($config['driver']).'.php') === false) {
			self::debug("Not found support driver in driver folder");
		}
		
		if (empty($config['database']) === true) {
			self::debug("Database info can not empty");
		}
		
		if ($config['host'] === null) {
			$config['host'] = "localhost";
		}

		$driver = new $config['driver']();
		$driver->connect($config['host'], $config['username'], $config['password'], $config['database'], $config['prefix'], $config['charset'], $config['port']);
		$driver->set_debug($config['debug']);
	
		return $driver;
	}
	
	private static function set_prefix($prefix) {
		self::$prefix = $prefix;
	}
	
	private static function debug($message, $type = "debug") {
		exit(sprintf("<p>[%s] %s</p>", $type, $message));
	}
	
}
?>