<?php
if (defined("IN_APPS") === false) exit("Access Dead");

class Plugin {
	private static $plugin_folder = "plugin";
	private static $plugin_filter = array();
	private static $current_filter= array();
	private static $plugin_action = array();
	private static $merged_filter = array();
	
	public static function init($settings = array()) {
		if (is_array($settings) === true && empty($settings) === false) {
			foreach($settings as $k => $v) {
				self::$$k = $v;
			}
		}

		foreach(glob(self::$plugin_folder."/*") as $file_path) {
			require_once $file_path;
		}
	}
	
	public static function add_filter($tag, $function_name, $priority = 10, $total_arguments = 1) {
		$unique_id = self::get_unique_id($tag, $function_name, $priority);
		self::$plugin_filter[$tag][$priority][$unique_id] = array(
			'function' => $function_name,
			'total_arguments' => $total_arguments
		);
		unset(self::$merged_filter[$tag]);
	}
	
	public static function apply_filter($tag, $value) {
		$arguments = array();
		self::$current_filter[] = $tag;
		
		if (isset(self::$plugin_filter[$tag]) === false) {
			array_pop(self::$current_filter);
			return $value;
		}
		
		if (isset(self::$merged_filter[$tag]) === false) {
			ksort(self::$plugin_filter[$tag]);	// 優先櫂, 10 是最晚執行, 1 是最先執行
			$merged_filter[$tag] = true;
		}
		
		reset(self::$plugin_filter[$tag]);
		
		if (empty($arguments)) {
			$arguments = func_get_args();
		}

		foreach(self::$plugin_filter[$tag] as $priority) {
			foreach($priority as $filter) {
				if (is_null($filter['function']) === false) {
					$arguments[1] = $value;
					$value = call_user_func_array($filter['function'], array_slice($arguments, 1, (int) $filter['total_arguments']));
				}
			}
		}
		
		array_pop(self::$current_filter);
		
		return $value;
	}
	
	public static function remove_filter($tag, $function_name, $priority = 10) {
		$unique_id = self::get_unique_id($tag, $function_name, $priority);
		
		if (isset(self::$plugin_filter[$tag][$priority][$function_name]) === true) {
			unset(self::$plugin_filter[$tag][$priority][$function_name]);
			
			if (empty(self::$plugin_filter[$tag][$priority])) {
				unset(self::$plugin_filter[$tag][$priority]);
			}
			
			unset(self::$merged_filter[$tag]);
		}
	}
	
	public static function remove_all_filter($tag, $priority = false) {
		if (isset(self::$plugin_filter[$tag]) === true) {
			if ($priority !== false && isset(self::$plugin_filter[$tag][$priority])) {
				unset(self::$plugin_filter[$tag][$priority]);
			}else{
				unset(self::$plugin_filter[$tag]);
			}
		}
		
		if (isset(self::$merged_filter[$tag]) === true) {
			unset(self::$merged_filter[$tag]);
		}
	}
	
	public static function add_action($tag, $function_name, $priority = 10, $total_arguments = 1) {
		return self::add_filter($tag, $function_name, $priority, $total_arguments);
	}
	
	public static function do_action($tag, $argument = '') {
		if (isset(self::$plugin_action) === false) {
			self::$plugin_action = array();
		}
		
		if (isset(self::$plugin_action[$tag]) === false) {
			self::$plugin_action[$tag] = 1;
		}else{
			++self::$plugin_action[$tag];
		}
		
		self::$current_filter[] = $tag;
		
		if (isset(self::$plugin_filter[$tag]) === false) {
			array_pop(self::$current_filter);
			return;
		}
		
		$arguments = array();
		if (is_array($argument) === true && count($argument) === 1 && isset($argument[0]) && is_object($argument[0])) {
			$arguments[] = &$argument[0];
		}else{
			$arguments[] = $argument;
		}
		
		for($i=2; $i<func_num_args(); $i++) {
			$arguments[] = func_get_arg($i);
		}

		if (isset(self::$merged_filter[$tag]) === false) {
			ksort(self::$plugin_filter[$tag]);
			self::$merged_filter[$tag] = true;
		}
		
		reset(self::$plugin_filter[$tag]);
		
		foreach(self::$plugin_filter[$tag] as $priority) {
			foreach($priority as $filter) {
				if (is_null($filter['function']) === false) {
					call_user_func_array($filter['function'], array_slice($arguments, 0, (int) $filter['total_arguments']));
				}
			}
		}
		
		array_pop(self::$current_filter);
	}
	
	public static function remove_action($tag, $function_name, $priority = 10, $total_arguments = 1) {
		return self::remove_filter($tag, $function_name, $priority, $total_arguments);
	}
	
	public static function remove_all_actions($tag, $priority = false) {
		return self::remove_all_filter($tag, $priority);
	}
	
	private static function get_unique_id($tag, $object, $priority) {
		static $filter_id_counter = 0;
		
		if (is_string($object)) {
			return $object;
		}
		
		if (is_object($object)) {
			$object = array($object, '');
		}else{
			$object = (array) $object;
		}
		
		if (is_object($object[0])) {
			if (function_exists("spl_object_hash")) {
				return spl_object_hash($object[0]).$object[1];
			}else{
				$class_hash = get_class($object[0]).$object[1];
				
				// 如果內部有指定插件的識別碼,則將以 "類別名+識別碼" 返回
				if (isset($object[0]->filter_id) === true) {
					$class_hash .= $object[0]->filter_id;
				}else{					
					$class_hash .= isset(self::$plugin_filter[$tag][$priority]) ? count(self::$plugin_filter[$tag][$priority]) : $filter_id_counter;
					$object[0]->filter_id = $class_hash;
					++$filter_id_counter;
				}
				
				return $class_hash;
			}
		}else if ( is_string($object[0]) ) {
			return $object[0].$object[1];	// 物件的靜態呼叫
		}
	}	
	
}
?>