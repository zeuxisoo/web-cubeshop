<?php
if (defined("IN_APPS") === false) exit("Access Dead");

class Session {
	private static $begin = 0;
	private static $instance = null;
	
	public static function init() {
		if (isset($_SESSION) === false) {
			self::$instance = new Session();
			session_start();
		}
	}

	public static function set($name, $value) {
		$_SESSION[$name] = $value;
	}

	public static function get($name, $once = false) {
		$value = Request::session($name);
		if (isset($_SESSION[$name]) === true && $once === true) {
			unset($_SESSION[$name]);
		}
		return $value;
	}
}
?>