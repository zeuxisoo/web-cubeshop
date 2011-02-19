<?php
require_once dirname(dirname(dirname(__FILE__))).'/kernel/init.php';

Permission_Helper::need_admin();

// Define defails
$page_info['page_title']['add'] = "Create Sales History";
$page_info['page_title']['edit'] = "Edit Sales History";

// Action Handles
// Share parts
if ($action == "create" || $action == "update") {
	$cube_id = Request::post("cube_id");
	$product_id = Request::post("product_id");
	$qty = Request::post("qty");
	$total_price = Request::post("total_price");
	$remark = Request::post("remark");
	$status = Request::post("status");

	if (empty($cube_id) === true) {
		Session::set("error", "Please select cube first");
	}elseif (empty($product_id) === true) {
		Session::set("error", "Please select product first");
	}elseif (empty($qty) === true) {
		Session::set("error", "Please enter quantity");
	}elseif ($total_price == "") {
		Session::set("error", "Please enter total price");
	}elseif (empty($status) === true) {
		Session::set("error", "Please select status first");
	}
	
	$test = Session::get("error");
	
	if (empty($test) === false) {
		exit(Util::redirect(PHP_SELF));
	}
}

// Create & Update & Normal
if ($action == "create") {		

	// Create record
	$table = new Table("sales_history");
	$table->cube_id = $cube_id;
	$table->product_id = $product_id;
	$table->qty = $qty;
	$table->total_price = $total_price;
	$table->remark = $remark;
	$table->status = $status;
	$table->create_date = time();
	$table->insert();
	
	Session::set("success", "New sale history was added");
	
	Util::redirect(PHP_SELF);

}elseif ($action == "update") {

	$id = Request::post("id");
	
	if (empty($id) === true) {
		Session::set("error", "Not condition provided");
	}elseif (is_numeric($id) === false) {
		Session::set("error", "Condition not correct");
	}else{
		
		// Create record
		$table = new Table("sales_history", $id);
		$table->cube_id = $cube_id;
		$table->product_id = $product_id;
		$table->qty = $qty;
		$table->total_price = $total_price;
		$table->remark = $remark;
		$table->status = $status;
		$table->renew();
		
		Session::set("success", "Sale history info was updated");
		exit(Util::redirect(Util::make_url(PHP_SELF, array('id' => $id))));
	}

	Util::redirect(PHP_SELF);
	
}else{

	$id = Request::get("id");

	// Edit mode
	if (empty($id) === false && is_numeric($id) === true) {
		// Query member info by cube's id
		$cubes = Table::fetch_one_by_column("sales_history", $id);
		
		// Check is or not found out cube
		if (empty($cubes['id']) === true) {
			Session::set("error", "Not found record");
			Util::redirect(PHP_SELF);
		}else{
			$hidden = array(
				array('name' => 'action', 'value' => 'update'),
				array('name' => 'id', 'value' => $id)
			);
			$source = Util::fill_value_for_key(Table::get_columns("sales_history"), $cubes);
			
			$page_title = $page_info['page_title']['edit'];
		}
	}else{
		// Normal mode
		$hidden = array();
		$source = Util::fill_value_for_key(Table::get_columns("sales_history"));
		
		$page_title = $page_info['page_title']['add'];
	}
	
	//
	$cube_id = Request::get("cube_id");
	
	//
	if (empty($cube_id) === true) {
		$sql_condition = array();
	}else{
		$source['cube_id'] = $cube_id;
		$sql_condition = array("cube_id" => $cube_id);
	}
	
	// Query Cube
	$records = Table::fetch_all("cubes", array("select" => "id, cube_name"));
	$cubes = array();
	foreach($records as $record) {
		$cubes[$record['id']] = $record['cube_name'];
	}
	
	// Query Cube Product
	$records = Table::fetch_all("products", array(
		"select" => "id, name", 
		"where" => $sql_condition
	));
	$products = array();
	foreach($records as $record) {
		$products[$record['id']] = $record['name'];
	}

	// Make a change event on "Under cube" select menu
	Module::create("content")->add("header_script", '
		$(function() {
			$("select[name=cube_id]").change(function() {
				window.location = PHP_SELF + "?cube_id=" + $(this).val();
			});
		});
	');

	//
	Form::bulid(Form::IS_DETAIL, Form_View::create_detail($page_title, array(
		Form_View::select("Under Cube", "cube_id", $cubes, $source['cube_id']),
		Form_View::select("Product Name", "product_id", $products, $source['product_id']),
		Form_View::input("Quantity of sale", "qty", $source['qty']),
		Form_View::input("Total Price", "total_price", $source['total_price']),
		Form_View::textarea("Remark", "remark", $source['remark']),
		
		Form_view::select("Status?", "status", array(
			'sale' => "Sale",
			'refund' => "Refund",
			'cancel' => "Cacnel",
		), $source['status']),
	), $hidden));
	
}
?>