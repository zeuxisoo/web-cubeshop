<?php
require_once dirname(dirname(dirname(__FILE__))).'/kernel/init.php';

Permission_Helper::need_client();

// In change status mode
if ($action == "change-status") {

	$id = Request::get("id");
	$type = Request::get("type");
	$page = Request::get("page");
	
	if (strtolower($type) == "sale") {
		Session::set("error", "Can not set to sale");
	}else{
	
		if (is_numeric($id) === true) {
			$table = new Table("sales_history", $id);
			$table->status = $type;
			$table->renew();
			
			Session::set("success", "Updated record to .... $type");
		}else{
			Session::set("error", "Can not update record");
		}
	
	}

	Util::redirect(PHP_SELF, array("page" => $page));

}else{

	// Define defails
	$page_info['page_title']['list'] = "Sales History List";
	$page_info['page_title']['search'] = "Search In Sales History List";
	$page_info['per_page_row'] = 12;
	
	// Load form core
	Form::init();
	
	// If search mode
	$sql_condition  = 1;
	$sql_condition .= " AND sh.cube_id = $client[id]";
	if ($action == "search") {
		
		// Get data
		$product_id = Request::get("product_id");
		$qty = Request::get("qty");
		$status = Request::get("status");
	
		// Make query condition
		$sql_condition .= empty($product_id) === false ? " AND sh.product_id = '$product_id'" : "";
		$sql_condition .= empty($qty) === false ? " AND sh.qty = '$qty'" : "";
		$sql_condition .= empty($status) === false ? " AND sh.status = '$status'" : "";

		//
		$page_title = $page_info['page_title']['search'];
	
	}else{
	
		$page_title = $page_info['page_title']['list'];
		
	}
	
	// Get total rent cubes
	$total_sales_history = $db->result($db->query("
		SELECT COUNT(1)
		FROM ".Table::table("sales_history")." sh
		LEFT JOIN ".Table::table("cubes")." c ON sh.cube_id = c.id
		LEFT JOIN ".Table::table("products")." p ON sh.product_id = p.id
		WHERE $sql_condition
	"), 0);
	
	// Paginate
	$paginate = new Paginate($total_sales_history, $page_info['per_page_row']);
	
	// Query Data	
	$sales_history = $db->fetch_all("
		SELECT sh.*, c.cube_name, p.name AS product_name
		FROM ".Table::table("sales_history")." sh
		LEFT JOIN ".Table::table("cubes")." c ON sh.cube_id = c.id
		LEFT JOIN ".Table::table("products")." p ON sh.product_id = p.id
		WHERE $sql_condition
		ORDER BY create_date DESC
		LIMIT ".$paginate->offset.", $page_info[per_page_row]
	");
	
	// Setup form rows
	$rent_cube_rows = array();
	foreach($sales_history as $row) {
		$manage_base_url =  array("id" => $row['id'], "action" => "change-status", "page" => $paginate->page_no);
	
		$rent_cube_rows[$row['id']] = array(
			$row['cube_name'], $row['product_name'], $row['qty'], $row['total_price'], Util::to_date_time($row['create_date'], "Y-m-d H:i:s"), ucfirst($row['status']), 
			
			Form_View::links(array(
				"Refund" => Util::make_url(PHP_SELF, $manage_base_url + array("type" => "refund")),
				"Cancel" => Util::make_url(PHP_SELF, $manage_base_url + array("type" => "cancel")),
			))
		);
	}
	
	// Create Form View
	Form::bulid(Form::IS_LIST, Form_View::create_list($page_title, array(
		array('Under Cube', 'Product', 'Quantity', 'Total Price', 'Creation Date', 'Status', '15%' => 'Manage'),
		$rent_cube_rows
	), $paginate->build_page_bar(), false));

}
?>