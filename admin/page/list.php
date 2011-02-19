<?php
require_once dirname(dirname(dirname(__FILE__))).'/kernel/init.php';

Permission_Helper::need_admin();

// Define defails
$page_info['page_title'] = "Page List";
$page_info['per_page_row'] = 12;

// Load form core
Form::init();

// Get total pages
$total_cubes = Table::count("pages");

// Paginate
$paginate = new Paginate($total_cubes, $page_info['per_page_row']);

// Query Data
$pages = Table::fetch_all("pages", array(
	"order" => "ORDER BY id ASC",
	"offset" => $paginate->offset,
	"size" => $page_info['per_page_row']
));

// Setup form rows
$page_rows = array();
foreach($pages as $row) {
	$page_rows[$row['id']] = array(
		$row['name'], Form_View::manage($row['id'])
	);
}

// Create Form View
Form::bulid(Form::IS_LIST, Form_View::create_list($page_info['page_title'], array(
	array('Name', '10%' => 'Manage'),
	$page_rows
), $paginate->build_page_bar(), false));
?>