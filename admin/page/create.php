<?php
require_once dirname(dirname(dirname(__FILE__))).'/kernel/init.php';

Permission_Helper::need_admin();

// Define defails
$page_info['page_title']['add'] = "Create Page";
$page_info['page_title']['edit'] = "Edit Page";

// Action Handles
// Share parts
if ($action == "create" || $action == "update") {
	$name = Request::post("name");
	$content = Request::post("content");
	
	if (empty($name) === true) {
		Session::set("error", "Please enter name");
	}elseif (Util::utf8_string_length($name) < 6 && Util::utf8_string_length($name) < 20) {
		Session::set("error", "Name must between 4 and 20 character");
	}elseif (preg_match("/([0-9A-Za-z])+/", $name) === false) {
		Session::set("error", "Name must english & number character");
	}
	
	$test = Session::get("error");
	
	if (empty($test) === false) {
		exit(Util::redirect(PHP_SELF));
	}
}

// Create & Update & Normal
if ($action == "create") {
	
	// Create record
	$table = new Table("pages");
	$table->name = $name;
	$table->content = $content;
	$table->insert();
	
	Session::set("success", "New page was added");
		
	Util::redirect(PHP_SELF);

}elseif ($action == "update") {

	$id = Request::post("id");
	
	if (empty($id) === true) {
		Session::set("error", "Not condition provided");
	}elseif (is_numeric($id) === false) {
		Session::set("error", "Condition not correct");
	}else{
		// Create record
		$table = new Table("pages", $id);
		$table->name = $name;
		$table->content = $content;
		$table->renew();
		
		Session::set("success", "Page info was updated");		
		exit(Util::redirect(Util::make_url(PHP_SELF, array('id' => $id))));
	}

	Util::redirect(PHP_SELF);
	
}else{

	$id = Request::get("id");

	// Edit mode
	if (empty($id) === false && is_numeric($id) === true) {
		// Query page info by cube's id
		$cubes = Table::fetch_one_by_column("pages", $id);
		
		// Check is or not found out cube
		if (empty($cubes['id']) === true) {
			Session::set("error", "Not found record");
			Util::redirect(PHP_SELF);
		}else{
			$hidden = array(
				array('name' => 'action', 'value' => 'update'),
				array('name' => 'id', 'value' => $id)
			);
			$source = Util::fill_value_for_key(Table::get_columns("pages"), $cubes);
			
			$page_title = $page_info['page_title']['edit'];
			$attributes['input'] = array('readonly' => 'readonly');
		}
	}else{
		// Normal mode
		$hidden = array();
		$source = Util::fill_value_for_key(Table::get_columns("pages"));
		
		$page_title = $page_info['page_title']['add'];
		$attributes['input'] = array();
	}

	Form::bulid(Form::IS_DETAIL, Form_View::create_detail($page_title, array(
		Form_View::input(
			"Name", "name", $source['name'], 
			"validate[required,length[4,20]]", "Must be 4~20 english character & number combination", 
			Form_View::TEXT_FIELD, $attributes['input']
		),		
		Form_View::textarea("content", "content", $source['content']),
	), $hidden));
	
}
?>