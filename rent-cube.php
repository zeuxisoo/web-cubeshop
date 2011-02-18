<?php
require_once dirname(__FILE__).'/kernel/init.php';

if ($action === "rent") {

	$contact_person = Request::post("contact_person");
	$telephone = Request::post("telephone");
	$email = Request::post("email");
	$expected_price = Request::post("expected_price");
	
	if (empty($contact_person) === true) {
		Session::set("error", "Please enter contact person");
	}elseif (empty($telephone) === true) {
		Session::set("error", "Please enter telephone");
	}elseif (empty($email) === true) {
		Session::set("error", "Please enter email");
	}elseif (Util::is_email($email) === false) {
		Session::set("error", "Email format not correct");
	}elseif (empty($expected_price) === true) {
		Session::set("error", "Please enter expected price");
	}else{
	
		$table = new Table("rent_cubes");
		$table->contact_person = $contact_person;
		$table->telephone = $telephone;
		$table->email = $email;
		$table->expected_price = $expected_price;
		$table->create_date = time();
		$table->insert();
		
		Session::set("success", "Thank for your request, We will contact you as soon as possible");	
	}

	Util::redirect(PHP_SELF);

}else{

	include_once View::display("rent-cube.html");
	
}
?>