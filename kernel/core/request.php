<?php
if (defined("IN_APPS") === false) exit("Access Dead");

class Request {

	public static function get($key, $default_value = "") {
		return isset($_GET[$key]) ? $_GET[$key] : $default_value;
	}

	public static function post($key, $default_value = "") {
		return isset($_POST[$key]) ? $_POST[$key] : $default_value;
	}

	public static function cookie($key, $default_value = "") {
		return isset($_COOKIE[$key]) ? $_COOKIE[$key] : $default_value;
	}

	public static function session($key, $default_value = "") {
		return isset($_SESSION[$key]) ? $_SESSION[$key] : $default_value;
	}
	
	public static function file($key) {
		return $_FILES[$key];
	}
	
	public static function gpc($key, $default_value = "") {
		$get_value = self::get($key, $default_value);
		$post_value = self::post($key, $default_value);
		$cookie_value = self::cookie($key, $default_value);
		
		if (empty($get_value) === false) {
			return $get_value;
		}else if (empty($post_value) === false) {
			return $post_value;
		}else if (empty($cookie_value) === false) {
			return $cookie_value;
		}else{
			return "";
		}
	}

}
?>