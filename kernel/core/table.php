<?php
if (defined("IN_APPS") === false) exit("Access Dead");

class Table {

	private static $db = null;
	private $table_name = "";
	private $primary_key_name = 0;
	private $primary_key_value = 0;
	private $column_values = array();
	private $last_insert_id = 0;

	public function __construct($table_name, $primary_key_value = "", $primary_key_name = "id") {
		$this->table_name = $table_name;

		if (empty($primary_key_value) === false) {
			$this->set_primary_key($primary_key_name, $primary_key_value);
		}
	}

	public function __set($key, $value) {
		$this->column_values[$key] = $value;
	}

	public function set_primary_key($key, $value) {
		$this->primary_key_name = $key;
		$this->primary_key_value = $value;
	}

	public function init($db_object) {
		self::$db = $db_object;
	}
	
	public function insert() {
		if (empty($this->column_values) === false) {
			$data_table = array();
			foreach($this->column_values as $key => $value) {
				$data_table[$key] = self::$db->escape($this->column_values[$key]);
			}
			
			$key_string = "`".implode("`, `", array_keys($data_table))."`";
			$value_string = "'".implode("','", array_values($data_table))."'";

			self::$db->update("INSERT INTO ".self::table($this->table_name)." (".$key_string.") VALUES (".$value_string.")");

			$this->last_insert_id = self::$db->get_last_insert_id();
			
			return $this->last_insert_id;
		}		
	}
	
	public function renew() {
		if (empty($this->primary_key_value) === false && empty($this->column_values) === false) {
			$data_table = array();
			foreach($this->column_values as $key => $value) {
				$data_table[$key] = self::$db->escape($this->column_values[$key]);
			}

			$condition = array(
				"where" => array($this->primary_key_name => $this->primary_key_value),	
			);
			self::update($this->table_name, $data_table, $condition);
		}
	}

	// Extra method
	public static function fetch_all($table_name, $condition = array()) {
		$select= isset($condition['select']) === false ? "*" : $condition['select'];
		$where = self::build_where($condition);
		$order = isset($condition['order']) === false ? "" : $condition['order'];
		$offset= isset($condition['offset']) === false ? null : abs(intval($condition['offset']));

		$is_one= isset($condition['one']) === false ? false : $condition['one'];
		if ($is_one === true) {
			$size = 1;
		}else{
			$size = isset($condition['size']) === false ? null : abs(intval($condition['size']));
		}

		if ($offset !== null && $size !== null) {
			$limit = "LIMIT {$offset}, {$size}";
		}else{
			$limit = "";
		}

		$sql = "
			SELECT {$select} 
			FROM ".self::table($table_name)." 
			{$where}
			{$order}
			{$limit}
		";

		if ($is_one) {
			return self::$db->fetch_one($sql);
		}else{
			return self::$db->fetch_all($sql);
		}
	}
	
	public static function update($table_name, $data_table, $condition = array()) {
		$update_table = array();
		if (is_array($data_table)) {
			foreach($data_table as $key => $value) {
				$update_table[] = "`{$key}` = '{$value}'";
			}
		}
		$update_string = implode(", ", $update_table);

		$where = self::build_where($condition);
		
		self::$db->update("
			UPDATE ".self::table($table_name)."
			SET {$update_string}
			{$where}
		");
	}
	
	public static function count($table_name, $where = '', $count_by_key = '1') {
		if ($where != "" && stristr(strtolower($where), "where") === false) {
			$where = "WHERE {$where}";
		}else if (is_array($where) === true) {
			$where = self::build_where(array("where" => $where));
		}

		$row = self::$db->result(
			self::$db->query("
				SELECT COUNT(".$count_by_key.") AS count 
				FROM ".self::table($table_name)." 
				{$where}
			")
		, 0);

		return intval($row);
	}
	
	public static function fetch_one_by_column($table_name, $column_value, $column_name = 'id') {
		$condition = array(
			"where" => array($column_name => $column_value),
			"one" => true,
		);
		return self::fetch_all($table_name, $condition);
	}	
	
	public static function get_columns($table_name) {
		$fields = array();
		$query = self::$db->query("SHOW COLUMNS FROM ".self::table($table_name));
		while($row = self::$db->fetch_array($query)) {
			$fields[] = $row['Field'];
		}
		return $fields;
	}
	
	public static function delete($table_name, $where = array()) {
 		$where = self::build_where(array("where" => $where));
		$where = empty($where) === false ? $where : "";
		$sql = "DELETE FROM ".self::table($table_name)." $where";

		return self::$db->update($sql);
	}
	
	public static function get_last_insert_id() {
		return self::$db->get_last_insert_id();
	}
	
	public static function escape($data) {
		return self::$db->escape($data);
	}
	
	// Build-In method
	public static function table($table_name) {
		return self::$db->prefix.$table_name;
	}
	
	private static function build_where($condition) {
		if (isset($condition['where']) === false) {
			return "";
		}else{
			$where = $condition['where'];
			$where_logic = isset($condition['where_logic']) === false ? "AND" : $condition['where_logic'];

			$where_string = array();
			if (is_array($where)) {
				foreach($where as $key => $value) {
					$where_string[] = " `{$key}` = '".self::escape($value)."'";
				}
				$where_string = implode(" ".$where_logic." ", $where_string);
			}else{
				$where_string = $where;
			}

			if (empty($where_string) === false && stristr(strtolower($where_string), "where") === false) {
				$where_string = "WHERE {$where_string}";
			}

			return $where_string;
		}
	}
}
?>