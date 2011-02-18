<?php
require_once dirname(dirname(dirname(__FILE__))).'/kernel/init.php';

Util::need_admin();

// Define defails
$page_info['page_title']['search'] = "Search in Rent Cube";

if ($action == "search") {

	// Get data
	$cube_id = Request::post("cube_id");
	$product_id = Request::post("product_id");
	$qty = Request::post("qty");
	$status = Request::post("status");

	// Connect to list and enable it to search mode
	Util::redirect(Module::create("script")->get("list"), array(
		"action" => "search",
		"cube_id" => $cube_id,
		"product_id" => $product_id,
		"qty" => $qty,
		"status" => $status,
	));	

}else{
	
	// Query Cubes
	$records = Table::fetch_all("cubes", array("select" => "id, cube_name"));
	$cubes = array("" => "");
	foreach($records as $record) {
		$cubes[$record['id']] = $record['cube_name'];
	}
	
	// Query Products
	$records = Table::fetch_all("products", array("select" => "id, name"));
	$products = array("" => "");
	foreach($records as $record) {
		$products[$record['id']] = $record['name'];
	}
	
	// Load form core
	Form::bulid(Form::IS_DETAIL, Form_View::create_detail($page_info['page_title']['search'], array(
		Form_View::select("Under Cube", "cube_id", $cubes),
		Form_View::select("Product", "product_id", $products),
		Form_View::input("Quantity of sale", "qty"),

		Form_view::select("Status", "status", array(
			'' => "",
			'sale' => "Sale",
			'refund' => "Refund",
			'cancel' => "Cancel"
		)),
	), array(
		array("name" => "action", "value" => "search"),
	)));

}
?>