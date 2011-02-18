<?php
if (defined("IN_APPS") === false) exit("Access Dead");

class Util {
	
	public static function auto_quote($string, $force = 0) {
		if(get_magic_quotes_gpc() == false || $force) {
			if(is_array($string)) {
				foreach($string as $key => $val) {
					$string[$key] = self::auto_quote($val, $force);
				}
			} else {
				$string = addslashes($string);
			}
		}
		return $string;
	}
	
	public static function get_php_self() {
		$php_self =	isset($_SERVER['PHP_SELF'])	? $_SERVER['PHP_SELF'] : $_SERVER['SCRIPT_NAME'];
		if (substr($php_self, -1) == '/') {
			$php_self .= 'index.php';
		}
		return $php_self;
	}

	public static function get_php_uri() {
		if (isset($_SERVER['SCRIPT_URI'])) {
			$script_uri = $_SERVER['SCRIPT_URI'];
		}else{
			$script_uri = $_SERVER['REQUEST_URI'];
		}
		return $script_uri;
	}

	public static function is_debug() {
		return defined("ENABLE_DEBUG_MODE") && ENABLE_DEBUG_MODE === true;
	}

	public static function end_with($haystack, $needle, $case=true) {
		if ($case) {
			return (strcmp(substr($haystack, strlen($haystack) - strlen($needle)),$needle)===0);
		}
		return (strcasecmp(substr($haystack, strlen($haystack) - strlen($needle)),$needle)===0);
	}

	public static function cut_str($text, $start, $limit, $encode = 'UTF-8') {
		if (function_exists("mb_substri")) {
			$sub_text = mb_substr($text, $start, $limit, $encode);
		}else{
			$sub_text = preg_replace(
				'#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$start.'}'.'((?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){0,'.$limit.'}).*#s',
				'$1',
				$text
			);
		}

		$text_length = self::utf8_string_length($text);
		$sub_text_length = self::utf8_string_length($sub_text);

		return ($sub_text_length < $text_length) ? $sub_text." ..." : $sub_text;
	}

	public static function utf8_string_length($string) {
		if (function_exists("mb_strlen")) {
			return mb_strlen($string, "UTF-8");
		}else if (function_exists("preg_match_all")) {
			preg_match_all("/./us", $string, $match);
			return count($match[0]);
		}else{
			$byte_length = strlen($string);
			$count = 0;
			
			for ($i = 0; $i < $byte_length; $i++){
				if ((ord($str[$i]) & 192) == 128) {
					continue;
				}
				$count++;
			}
			return $count;
		}
	}
	
	public static function get_client_ip() {
		if(getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
			$online_ip = getenv('HTTP_CLIENT_IP');
		} elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
			$online_ip = getenv('HTTP_X_FORWARDED_FOR');
		} elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
			$online_ip = getenv('REMOTE_ADDR');
		} elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
			$online_ip = $_SERVER['REMOTE_ADDR'];
		}else{
			$online_ip = "0.0.0.0";
		}
		return preg_replace("/^([\d\.]+).*/", "\\1", $online_ip);
	}
	
	public static function get_random_string($length) {
		$random_string = array();
		$chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz';
		$max = strlen($chars)-1;
		
		mt_srand((double)microtime() * 1000000);
		for($i=0; $i<$length; $i++) {
			$random_string[] = $chars[mt_rand(0, $max)];
		}
		unset($chars, $max);
		
		return join("", $random_string);
	}
	
	public static function add_cookie($name, $value, $time_out = 3600, $path = '/', $domain = '') {
		setcookie($name, $value, $time_out, $path, $domain, ($_SERVER['SERVER_PORT'] == 443 ? 1 : 0));
	}
	
	public static function remove_cookie($name) {
		self::add_cookie($name, '', -84600);
		
		if (isset($_COOKIE[$name])) {
			unset($_COOKIE[$name]);
		}
	}
	
	public static function to_date_time($time_stamp, $format = 'Y-m-d', $time_zone = 8) {
		return gmdate($format, $time_stamp + $time_zone * 3600);
	}
	
	public static function redirect($url, $query_string = array(), $time = 0, $message = '') {
		$url = str_replace(array("\n", "\r"), '', $url);
		
		if (empty($query_string) === false) {
			$url .= "?".http_build_query($query_string);
		}
		
		if(empty($message) === true) {
			$message = "{$time}s will auto redirect to {$url}?I";
		}
		
		if (headers_sent() === false) {
			header("Content-Type:text/html; charset=utf-8");
			
			if($time === 0) {
				header("Location: ".$url);
			}else{
				header("refresh:{$time};url={$url}");
			}
			
			exit();
		}else{
			$string = "<meta http-equiv='Refresh' content='{$time};URL={$url}'>";
			
			if($time != 0) {
				$string .= $message;
			}
			
			exit($string);
		}
	}

	public static function to_time_stamp($date_time, $sep_datetime = ' ', $sep_date = '-', $sep_time = ':') {
		$time_table = explode($sep_datetime, $date_time);

		if (count($time_table) == 1) {
			list($yy, $mm, $dd) = explode($sep_date, $time_table[0]);
			
			if (empty($yy) || empty($mm) || empty($dd)) {
				return "(ERROR FORMAT)";
			}else{
				return mktime(0, 0, 0, $mm, $dd, $yy);
			}
		}else{
			list($yy, $mm, $dd) = explode($sep_date, $time_table[0]);
			list($hh, $ii, $ss) = explode($sep_time, $time_table[1]);

			if (empty($yy) || empty($mm) || empty($dd)) {
				return "(ERROR FORMAT -> Date)";
			}elseif (empty($hh) || empty($ii) || empty($ss)) {
				return "(ERROR FORMAT -> Time)";
			}else{
				return mktime($hh, $ii, $ss, $mm, $dd, $yy);
			}
		}
	}
	
	public static function is_ajax() {
		return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && ($_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest'));
	}
	
	public static function make_auth($string, $operation = 'ENCODE') {
		$string = $operation == 'DECODE' ? base64_decode($string) : base64_encode($string);
		return $string;
	}
	
	public static function is_admin() {
		global $config;
		
		$admin_auth = Request::cookie($config['admin']['cookie_auth_name']);

		if (isset($admin_auth) === true && empty($admin_auth) === false) {
			list($admin_username, $admin_password, $admin_auth_key) = explode("\t", Util::make_auth($admin_auth, "DECODE"));
			
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
	
	public static function make_url($url, $parameters = array()) {
		return empty($parameters) === true ? $url : $url."?".http_build_query($parameters);
	}
		
	public static function send_mail($contents, $attachment = array()) {
		$to_mail = $contents['to_mail'];
		$subject = $contents['subject'];
		$message = $contents['message'];
		$to_user = isset($contents['to_user']) ? $contents['to_user'] : "Dear Sir/Madam";
	
		require_once LIBRARY_ROOT.'/phpmailer/phpmailer.inc.php';
	
		$mail = new PHPMailer();

		if (MAIL_SMTP_ENABLE === true) {
			$mail->IsSMTP();
		
			if (MAIL_SMTP_SECURE != '') {
				$mail->SMTPSecure = MAIL_SMTP_SECURE;
			}
			
			$mail->SMTPAuth = MAIL_SMTP_AUTH;
			$mail->Host     = MAIL_SMTP_HOST;
			$mail->Username = MAIL_SMTP_USER;
			$mail->Password = MAIL_SMTP_PASS;
			$mail->Port     = MAIL_SMTP_PORT;
		}
	
		$mail->IsHTML(true);
		$mail->CharSet  = MAIL_CHARSET;
		$mail->From     = MAIL_FROM_ADDRESS;
		$mail->FromName = MAIL_FROM_USERNAME;
		$mail->Subject  = $subject;
		$mail->Body     = $message;
	
		$mail->AddAddress($to_mail, $to_user);

		if (is_array($attachment) && !empty($attachment)) {
			foreach($attachment as $files) {
				$mail->AddAttachment($files);
			}
		}

		return $mail->Send();
	}
	
	public function is_email($email) {
		return strlen($email) > 6 && preg_match("/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/", $email);
	}
	
	public function fill_value_for_key($keys, $default_value = "") {
		$temp = "";
		foreach($keys as $key) {
			if (is_string($default_value) === true) {
				$temp[$key] = $default_value;
			}else{
				$temp[$key] = isset($default_value[$key]) === true ? $default_value[$key] : "";
			}
		}
		return $temp;
	}

	public static function render_style($text, $css) {
		return sprintf("<span class='%s'>%s</span>", $css, $text);
	}
	
	public static function render_error($text) {
		return self::render_style($text, "error");
	}
	
	public static function render_success($text) {
		return self::render_style($text, "success");
	}
	
	public static function get_php_config($config_name) {
		switch($result = get_cfg_var($config_name)) {
			case 0:
				return self::render_error("OFF");
				break;
			case 1:
				return self::render_success("ON");
				break;
			default:
				return $result;
				break;
		}
	}
	
	public static function get_php_support($function_name) {
		return  false !== function_exists($function_name) ? self::render_success('Support') : self::render_error('Not Support');
	}

	public static function count_folder_size($dir) { 
		$dh = opendir($dir);
		$size = 0;
		while ($file = readdir($dh)) {
			if ($file != '.' && $file != '..') {
				$path = $dir."/".$file;
				if (@is_dir($path)) {
					$size += self::count_folder_size($path);
				} else {
					$size += filesize($path);
				}
			}
		}
		closedir($dh);
		return $size;
	}

	public static function size_format($size) {
		$sizes = array(" Bytes", " KB", " MB", " GB", " TB", " PB", " EB", " ZB", " YB");
		if ($size == 0) return 0; 
		return (round($size/pow(1024, ($i = floor(log($size, 1024)))), $i > 1 ? 2 : 0) . $sizes[$i]);
	}

}
?>