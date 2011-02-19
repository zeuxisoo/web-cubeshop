<?php
require_once dirname(dirname(dirname(__FILE__))).'/kernel/init.php';

Permission_Helper::need_admin();

// Define defails
$page_info['page_title']['list'] = "Product List";
$page_info['page_title']['search'] = "Search In Product List";
$page_info['per_page_row'] = 12;

// Load form core
Form::init();

// If search mode
$sql_condition  = 1;
if ($action == "search") {
	
	// Get data
	$cube_id = Request::get("cube_id");
	$product_name = Request::get("product_name");
	$is_display = Request::get("is_display");

	// Make query condition
	$sql_condition .= empty($cube_id) === false ? " AND cube_id = $cube_id" : "";
	$sql_condition .= empty($product_name) === false ? " AND name LIKE '%$product_name%'" : "";
	$sql_condition .= empty($is_display) === false ? " AND is_display = '$is_display'" : "";
	
	//
	$page_title = $page_info['page_title']['search'];
	
}else{

	$page_title = $page_info['page_title']['list'];
	
}

// Get total products
$total_products = Table::count("products", $sql_condition);

// Paginate
$paginate = new Paginate($total_products, $page_info['per_page_row']);

// Query Data
$products = Table::fetch_all("products", array(
	"order" => "ORDER BY create_date DESC",
	"offset" => $paginate->offset,
	"size" => $page_info['per_page_row'],
	"where" => $sql_condition,
));

// Query Cube
$records = Table::fetch_all("cubes", array("select" => "id, cube_name"));
$cubes = array();
foreach($records as $record) {
	$cubes[$record['id']] = $record['cube_name'];
}

// Setup form rows
$product_rows = array();
foreach($products as $row) {
	$cover = Form_View::preview('image', ATTACHMENT_URL.'/product-cover/'.$row['cover'], 50, 50);

	$product_rows[$row['id']] = array(
		$cubes[$row['cube_id']], $row['name'], $row['stock'], $row['price'], 
		Util::to_date_time($row['create_date']), $row['is_display'], $cover, Form_View::manage($row['id'])
	);
}

// Create Form View
Form::bulid(Form::IS_LIST, Form_View::create_list($page_title, array(
	array('Under Cube', 'Name', 'Stock', 'Price', 'Creation Date', 'Display?', '11%' => 'Product', '10%' => 'Manage'),
	$product_rows
), $paginate->build_page_bar(), false));
?>