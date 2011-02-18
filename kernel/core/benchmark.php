<?php
if (defined("IN_APPS") === false) exit("Access Dead");

class Benchmark {

	public static function start() {
		$time_parts = explode(" ",microtime());
		return $time_parts[1].substr($time_parts[0], 1);
	}


	public static function process_time($start_time) {
		$time_parts = explode(" ",microtime());
		$end_time = $time_parts[1].substr($time_parts[0], 1);
		return round($end_time - $start_time,6);
	}

}
?>