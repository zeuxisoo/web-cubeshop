<?php
require_once dirname(dirname(dirname(__FILE__))).'/kernel/init.php';

Util::need_client();

// Define defails
$page_info['page_title']['search'] = "Search in Rent Cube";

if ($action == "search") {

	// Get data
	$product_id = Request::post("product_id");
	$qty = Request::post("qty");
	$status = Request::post("status");

	// Connect to list and enable it to search mode
	Util::redirect(Module::create("script")->get("list"), array(
		"action" => "search",
		"product_id" => $product_id,
		"qty" => $qty,
		"status" => $status,
	));	

}else{
	
	// Query Products
	$records = Table::fetch_all("products", array(
		"select" => "id, name",
		"where" => array(
			"cube_id" => $client['id']
		)
	));
	$products = array("" => "");
	foreach($records as $record) {
		$products[$record['id']] = $record['name'];
	}
	
	// Load form core
	Form::bulid(Form::IS_DETAIL, Form_View::create_detail($page_info['page_title']['search'], array(
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