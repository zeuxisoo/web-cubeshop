<?php
require_once dirname(dirname(dirname(__FILE__))).'/kernel/init.php';

Permission_Helper::need_admin();

// In change status mode
if ($action == "change-status") {

	$id = Request::get("id");
	$type = Request::get("type");
	$page = Request::get("page");
	
	if (is_numeric($id) === true) {
		$table = new Table("rent_cubes", $id);
		$table->status = $type;
		$table->renew();
		
		Session::set("success", "Updated record to .... $type");
	}else{
		Session::set("error", "Can not update record");
	}

	Util::redirect(PHP_SELF, array("page" => $page));

}else{

	// Define defails
	$page_info['page_title']['list'] = "Rent Cube List";
	$page_info['page_title']['search'] = "Search In Rent Cube List";
	$page_info['per_page_row'] = 12;
	
	// Load form core
	Form::init();
	
	// If search mode
	$sql_condition  = 1;
	if ($action == "search") {
		
		// Get data
		$contact_person = Request::get("contact_person");
		$telephone = Request::get("telephone");
		$email = Request::get("email");
		$status = Request::get("status");
	
		// Make query condition
		$sql_condition .= empty($contact_person) === false ? " AND contact_person LIKE '%$contact_person%'" : "";
		$sql_condition .= empty($telephone) === false ? " AND telephone LIKE '%$telephone%'" : "";
		$sql_condition .= empty($email) === false ? " AND email LIKE '%$email%'" : "";
		$sql_condition .= empty($status) === false ? " AND status = '$status'" : "";

		//
		$page_title = $page_info['page_title']['search'];
	
	}else{
	
		$page_title = $page_info['page_title']['list'];
		
	}
	
	// Get total rent cubes
	$total_rent_cubes = Table::count("rent_cubes", $sql_condition);
	
	// Paginate
	$paginate = new Paginate($total_rent_cubes, $page_info['per_page_row']);
	
	// Query Data
	$rent_cubes = Table::fetch_all("rent_cubes", array(
		"order" => "ORDER BY create_date DESC",
		"offset" => $paginate->offset,
		"size" => $page_info['per_page_row'],
		"where" => $sql_condition,
	));
	
	// Setup form rows
	$rent_cube_rows = array();
	foreach($rent_cubes as $row) {
		$manage_base_url =  array("id" => $row['id'], "action" => "change-status", "page" => $paginate->page_no);
	
		$rent_cube_rows[$row['id']] = array(
			$row['contact_person'], $row['telephone'], $row['email'], $row['expected_price'], Util::to_date_time($row['create_date'], "Y-m-d H:i:s"), ucfirst($row['status']), 
			
			Form_View::links(array(
				"Wait" => Util::make_url(PHP_SELF, $manage_base_url + array("type" => "wait")),
				"Rented" => Util::make_url(PHP_SELF, $manage_base_url + array("type" => "rented")),
				"Cancel" => Util::make_url(PHP_SELF, $manage_base_url + array("type" => "cancel")),
			))
		);
	}
	
	// Create Form View
	Form::bulid(Form::IS_LIST, Form_View::create_list($page_title, array(
		array('Contact Person', 'Telephone', 'Email', 'Expected Price', 'Request Date', 'Status', '15%' => 'Manage'),
		$rent_cube_rows
	), $paginate->build_page_bar(), false));

}
?>