<?php
if (defined("IN_APPS") === false) exit("Access Dead");

class Permission_Helper {

	public static function make_auth($string, $operation = 'ENCODE') {
		$string = $operation == 'DECODE' ? base64_decode($string) : base64_encode($string);
		return $string;
	}
	
	public static function is_admin() {
		global $config;
		
		$admin_auth = Request::cookie($config['admin']['cookie_auth_name']);

		if (isset($admin_auth) === true && empty($admin_auth) === false) {
			list($admin_username, $admin_password, $admin_auth_key) = explode("\t", self::make_auth($admin_auth, "DECODE"));
			
			return sha1($admin_username.$admin_password.$config['admin']['cookie_secure_key']) === $admin_auth_key;
		}
		return false;
	}
	
	public static function need_admin() {
		global $config;
		
		if (self::is_admin() === false) {
			Session::set("error", "Please login first");
			self::redirect($config['init']['site_url'].'/'.$config['admin']['login_page']);
		}
	}
	
	public static function is_client() {
		global $config;
		
		$client_auth = Request::cookie($config['client']['cookie_auth_name']);

		if (isset($client_auth) === true && empty($client_auth) === false) {
			list($client_username, $client_password, $client_auth_key) = explode("\t", self::make_auth($client_auth, "DECODE"));
			
			return sha1($client_username.$client_password.$config['client']['cookie_secure_key']) === $client_auth_key;
		}
		return false;
	}	
	
	public static function need_client() {
		global $config;
		
		if (self::is_client() === false) {
			Session::set("error", "Please login first");
			self::redirect($config['init']['site_url'].'/'.$config['client']['login_page']);
		}
	}

}
?>