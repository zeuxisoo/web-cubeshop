<?php
if (defined("IN_APPS") === false) exit("Access Dead");

// AutoLoad the Class
function __autoload($class_name) {
	static $class_path_instance	= array();
	
	if (in_array($class_name, $class_path_instance)	===	false) {
		foreach(array('core', 'driver', 'helper', 'model', 'library') as $folder) {
			$file_path = KERNEL_ROOT.'/'.$folder.'/'.strtolower($class_name).'.php';
			if (is_file($file_path)	&& file_exists($file_path))	{
				$class_path_instance[$class_name] =	$file_path;
				require_once $file_path;
				break;
			}
		}
	}
	
	return isset($class_path_instance[$class_name]) ? $class_path_instance[$class_name] : "";
}

function show_error($label, $message) {
	$label = "<strong>".$label.":</strong> ";
	exit($label.$message);
}

/*
 * Usage:
 * 		debug_trace("echo", "x", "y", ...);
 *		debug_trace("print_r", array("x"), array("y"), ...);
 *		debug_trace("var_dump", array("x"), array("y"), ...);
 */
function debug_it() {
	$arguments =  func_get_args();
	$display_mode = $arguments[0];
	$total_arguments = count($arguments);

	if (is_array($display_mode) === true) {
		$display_mode = "print_r";
	}

	if (is_array($arguments) === true) {
		foreach($arguments as $argument) {
			switch($display_mode) {
				case "echo":
					echo $argument;
					echo "<br />";
					break;
				case "var_dump":
					echo "<pre>";
					var_dump($argument);
					echo "</pre>";
					echo "<hr />";
					break;
				default:
					echo "<pre>";
					print_r($argument);
					echo "</pre>";
					echo "<hr />";
					break;
			}
		}
	}
	
	debug_back_trace(true);
}

function debug_back_trace($display_trace = false, $traces_to_ignore = 1) {
	$traces = debug_backtrace();
	$ret = array();
	foreach($traces as $i => $call){
		if ($i < $traces_to_ignore ) {
			continue;
		}

		$object = '';
		if (isset($call['class'])) {
			$object = $call['class'].$call['type'];
			if (is_array($call['args'])) {
				foreach ($call['args'] as &$arg) {
					func_get_args($arg);
				}
			}
		}        

		$ret[] = '#'
				.str_pad($i - $traces_to_ignore, 3, ' ')
				.$object.$call['function'].'('.implode(', ', $call['args'])
        		.') called at ['.$call['file'].':'.$call['line'].']';
    }

	if ($display_trace === true) {
		echo nl2br(implode("\n",$ret));
	}else{
		return implode("\n",$ret);
	}
}
?>
