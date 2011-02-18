<?php
if (defined("IN_APPS") === false) exit("Access Dead");

define('MODULE_INIT_FILE_NAME', '__init__.php');

class Module {

	protected static $settings = array(
		'module_root' => '',
		'module_url' => ''
	);

	public static function init($settings) {
		self::$settings = $settings;
		self::init_modules(self::get_modules());
	}
	
	public static function init_modules($modules) {
		foreach($modules as $name => $path) {
			$init_file = $path.'/'.MODULE_INIT_FILE_NAME;
			
			if (is_file($init_file) === true && file_exists($init_file)) {
				require_once $init_file;
			}
		}
	}
	
	public static function get_modules() {
		$modules = array();
		foreach(glob(self::$settings['module_root']."/*") as $file_path) {
			if (is_dir($file_path) === true) {
				$folder_name = basename($file_path);
				$modules[$folder_name] = $file_path;
			}
		}
		return $modules;
	}	
	
	public static function create($module_name) {
		static $loaded_modules = array();
		
		$module_name = "Module_".ucfirst($module_name);
				
		if (in_array($module_name, $loaded_modules) === false) {
			$loaded_modules[$module_name] = new $module_name();
		}

		return new $loaded_modules[$module_name];
	}
	
	public static function get_url($with_module_folder = '') {
		return self::$settings['module_url'].'/'.$with_module_folder;
	}
}

class Module_Navigation {

	private static $navigation = array();
	
	public function add($datas) {
		$priority = isset($datas['priority']) ? $datas['priority'] : 10;
		self::$navigation[$priority][$datas['id']] = $datas;
	}
	
	public function get() {
		ksort(self::$navigation);
		return self::$navigation;
	}
}

class Module_Script {
	
	private static $script = array(
		'create' => 'create.php',
		'list' => 'list.php',
		'edit' => 'create.php',
	);
	
	public function get($name) {
		return self::$script[$name];
	}
	
	public function add($key, $script_uri) {
		self::$script[$key] = $script_uri;
	}
}

class Module_Content {

	private static $content = array();

	public function add($name, $content, $priority = 10) {
		self::$content[$name][$priority][md5($name)] = $content;
	}
	
	public function show($name) {
		foreach(self::$content[$name] as $priority) {
			foreach($priority as $content) {
				echo $content;
			}
		}
	}

}
?>