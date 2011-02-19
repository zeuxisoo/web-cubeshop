<?php
require_once dirname(dirname(dirname(__FILE__))).'/kernel/init.php';

Permission_Helper::need_admin();

// Define defails
$page_info['page_title'] = "Cube List";
$page_info['per_page_row'] = 12;

// Load form core
Form::init();

// Get total members
$total_cubes = Table::count("cubes");

// Paginate
$paginate = new Paginate($total_cubes, $page_info['per_page_row']);

// Query Data
$cubes = Table::fetch_all("cubes", array(
	"order" => "ORDER BY create_date DESC",
	"offset" => $paginate->offset,
	"size" => $page_info['per_page_row']
));

// Setup form rows
$cube_rows = array();
foreach($cubes as $row) {
	$cube_cover = Form_View::preview('image', ATTACHMENT_URL.'/cube-cover/'.$row['cube_cover'], 50, 50);

	$cube_rows[$row['id']] = array(
		$row['login_name'], $row['contact_person'], $row['rent_price'], $row['rent_date'], $row['is_lock'], $row['is_display'], 
		$cube_cover, Form_View::manage($row['id'])
	);
}

// Create Form View
Form::bulid(Form::IS_LIST, Form_View::create_list($page_info['page_title'], array(
	array('Login Name', 'Contact Person', 'Rent_price', 'Rent_date', 'Lock?', 'Display?', 'Cube', '10%' => 'Manage'),
	$cube_rows
), $paginate->build_page_bar(), false));
?>