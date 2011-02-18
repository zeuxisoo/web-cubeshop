<?php
abstract class Driver {
	public $prefix = "";

	abstract public function connect($host, $username, $password, $database, $prefix, $charset, $port);
	abstract public function query($sql, $type = '');
	abstract public function update($sql, $type = '');
	abstract public function close();
	abstract public function set_debug($status);
	abstract public function get_debug_log();
	
	abstract public function get_error($sql);
	abstract public function get_error_no();
	
	abstract public function escape($data);
	abstract public function get_last_insert_id();
	
	abstract public function fetch_array($query, $type);
	abstract public function result($query, $column_number);
	abstract public function free_result($query);
	
	// extra method
	abstract public function fetch_one($sql, $type);
	abstract public function fetch_all($sql, $type);
}
?>