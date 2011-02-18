<?php
if (defined("IN_APPS") === false) exit("Access Dead");

define('FILE_NOT_FOUND', 1);
define('CONTENT_IS_EMPTY', 2);

class View {

	private static $view_folder			= 'template';
	private static $view_cache_folder	= 'cache/template';
	private static $theme				= '';
	private static $strip_tab			= true;
	private static $word_wrap			= true;
	private static $debug				= false;
	private static $view_access_string	= "IN_APPS";

	private static $view_content;
	private static $view_file_path;
	private static $view_file_cached_path;
	
	public static function set_settings($settings) {
		if (is_array($settings) === true && empty($settings) === false) {
			foreach($settings as $_key => $_value) {
				if (in_array($_key, array("view_folder", "view_cache_folder", "theme", "strip_tab", "word_wrap", "debug", "view_access_string")) === true) {
					self::$$_key = $_value;
				}
			}
		}
	}
	
	public static function display($view_file) {	
		if (self::get_compile_path($view_file, self::$theme) === true) {
			return self::$view_file_cached_path;
		}
	}

	private static function get_compile_path($view_file_name, $theme) {
		self::$theme = empty($theme) ? 'default' : $theme;
		
		// If not found setting's theme/file, then theme will set to "default theme"
		$view_file_folder = self::$view_folder.DIRECTORY_SEPARATOR.self::$theme;
		if (is_dir($view_file_folder) === false || file_exists($view_file_folder) === false) {
			self::get_compile_path($view_file_name, "default");
		}
		unset($view_file_folder);

		//
		self::$view_file_path = self::$view_folder.DIRECTORY_SEPARATOR.self::$theme.DIRECTORY_SEPARATOR.$view_file_name;
		self::$view_file_cached_path = self::$view_cache_folder.DIRECTORY_SEPARATOR.self::$theme.DIRECTORY_SEPARATOR.$view_file_name.'.php';

		$cache_directory = dirname(self::$view_file_cached_path);
		if (is_dir($cache_directory) === false && file_exists($cache_directory) === false) {
			mkdir($cache_directory, 0777, true);
		}

		if (is_file(self::$view_file_path) === false || file_exists(self::$view_file_path) === false) {
			self::error(FILE_NOT_FOUND);
		}else{

			if (file_exists(self::$view_file_cached_path) && (filemtime(self::$view_file_path) <= filemtime(self::$view_file_cached_path))) {
				return true;
			}else{
				self::$view_content = file_get_contents(self::$view_file_path);

				if (strlen(trim(self::$view_content)) <= 0) {
					self::error(CONTENT_IS_EMPTY);
				}else{

					$view_file_cached_theme_folder = self::$view_cache_folder.DIRECTORY_SEPARATOR.self::$theme;

					if (file_exists($view_file_cached_theme_folder) === false) {
						mkdir($view_file_cached_theme_folder, 0777);
					}
					unset($view_file_cached_theme_folder);

					self::compile();

					return true;
				}
			}
		
		}
	}

	private static function error($exception) {
		$message = '';

		switch($exception) {
			case FILE_NOT_FOUND:
				$message = "File not found";
				break;
			case CONTENT_IS_EMPTY:
				$message = "File content is empty";
		}

		if (self::$debug === true) {
			self::debug_log($message, self::$view_file_path);
			exit();
		}else{
			exit($message);
		}
	}

	private static function compile() {
		$var = '(\$[a-zA-Z_][a-zA-Z0-9_\->\.\[\]\'\$\(\)]*)';

		$search  = array(
					'#{(\$[a-zA-Z_][a-zA-Z0-9_\->\.\[\]\'\$\(\)]*)}#s',
					'#\$\{(.+?)\}#i',
					'#{set:(.+?)}#i',
					'#{_\(\"(.+?)\"\)}#i',
					'#<!--{include:(.*?)}-->#i',
					'#<!--{func:(.*?)}-->#i',
					'#<!--{echo_func:(.*?)}-->#i',
					'#<!--{foreach:(\S+)\s+(\S+)\s+(\S+)\}-->#i',
					'#<!--{foreach:(\S+)\s+(\S+)}-->#i',
					'#<!--{for:(.*?)\;(.*?)\;(.*?)}-->#i',
					'#<!--{if:(.*?)}-->#i',
					'#<!--{elseif:(.*?)}-->#i',
					'#<!--{else}-->#i',
					'#<!--{if:(.*?):(.*?):(.*?)}-->#i',
					'#<!--\${(.*?)}-->#ism',
				);

		$replace = array(
					'<?php echo \1; ?>',
					'<?php echo \1; ?>',
					'<?php \1; ?>',
					'<?php echo Language::get_text("\1"); ?>',
					'<?php include_once View::display(\'\1\'); ?>',
					'<?php \1; ?>',
					'<?php echo \1; ?>',
					'<?php if (is_array(\1)) { foreach(\1 as \2 => \3) { ?>',
					'<?php if (is_array(\1)) { foreach(\1 as \2) { ?>',
					'<?php for(\1;\2;\3) { ?>',
					'<?php if (\1) { ?>',
					'<?php } elseif (\1) { ?>',
					'<?php } else { ?>',
					'<?php echo (\1) ? \2 : \3; ?>',
					'<?php \1 ?>',
				);

		self::$view_content = preg_replace($search, $replace, self::$view_content);

		$search2 = array(
					'#<!--{/foreach}-->#i',
					'#<!--{/for}-->#i',
					'#<!--{/if}-->#i',
				);

		$replace2= array(
					'<?php } } ?>',
					'<?php } ?>',
					'<?php } ?>',
				);

		self::$view_content = preg_replace($search2, $replace2, self::$view_content);
		self::$view_content = self::$strip_tab === true ? preg_replace("/\t/s", "", self::$view_content) : self::$view_content;
		self::$view_content = self::$word_wrap === true ? self::$view_content : preg_replace("/([\n|\r|\r\n|\t]+)/s", "", self::$view_content);
		self::save();
	}

	private static function save() {
		file_put_contents(
			self::$view_file_cached_path, 
			"<?php if(!defined('".self::$view_access_string."')) exit('Access Denied'); ?>\n".self::$view_content
		);
	}
	
	private static function debug_log($error_no, $message) {
		echo "<strong>",$error_no,": </strong>",$message,"<br />";
	}

}
?>