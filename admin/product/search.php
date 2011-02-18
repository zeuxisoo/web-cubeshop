<?php
require_once dirname(dirname(dirname(__FILE__))).'/kernel/init.php';

Util::need_admin();

// Define defails
$page_info['page_title']['search'] = "Search in Product";

if ($action == "search") {

	// Get data
	$cube_id = Request::post("cube_id");
	$product_name = Request::post("product_name");
	$is_display = Request::post("is_display");

	// Connect to list and enable it to search mode
	Util::redirect(Module::create("script")->get("list"), array(
		"action" => "search",
		"cube_id" => $cube_id,
		"product_name" => $product_name,
		"is_display" => $is_display,
	));	

}else{

	// Query Cubes
	$records = Table::fetch_all("cubes", array("select" => "id, cube_name"));
	$cubes = array();
	foreach($records as $record) {
		$cubes[$record['id']] = $record['cube_name'];
	}
	
	// Load form core
	Form::bulid(Form::IS_DETAIL, Form_View::create_detail($page_info['page_title']['search'], array(
		Form_View::select("Under Cube", "cube_id", $cubes),
		Form_View::input("Product Name", "product_name"),
		
		Form_view::select("Display?", "is_display", array(
			'' => "",
			'N' => "No",
			'Y' => "Yes"
		)),
	), array(
		array("name" => "action", "value" => "search"),
	)));

}
?>