<?php
require_once dirname(dirname(dirname(__FILE__))).'/kernel/init.php';

Permission_Helper::need_admin();

// Define defails
$page_info['page_title']['search'] = "Search in Rent Cube";

if ($action == "search") {

	// Get data
	$contact_person = Request::post("contact_person");
	$telephone = Request::post("telephone");
	$email = Request::post("email");
	$status = Request::post("status");

	// Connect to list and enable it to search mode
	Util::redirect(Module::create("script")->get("list"), array(
		"action" => "search",
		"contact_person" => $contact_person,
		"telephone" => $telephone,
		"email" => $email,
		"status" => $status,
	));

}else{
	
	// Load form core
	Form::bulid(Form::IS_DETAIL, Form_View::create_detail($page_info['page_title']['search'], array(
		Form_View::input("Contact Person", "contact_person"),
		Form_View::input("telephone", "telephone"),
		Form_View::input("Email", "email"),
				
		Form_view::select("Status", "status", array(
			'' => "",
			'wait' => "Wait",
			'rented' => "Rented",
			'cancel' => "Cancel"
		)),
	), array(
		array("name" => "action", "value" => "search"),
	)));

}
?>