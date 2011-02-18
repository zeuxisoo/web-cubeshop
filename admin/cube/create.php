<?php
require_once dirname(dirname(dirname(__FILE__))).'/kernel/init.php';

Util::need_admin();

// Define defails
$page_info['page_title']['add'] = "Create Cube";
$page_info['page_title']['edit'] = "Edit Cube";

// Action Handles
// Share parts
if ($action == "create" || $action == "update") {
	$login_name = Request::post("login_name");
	$login_password = Request::post("login_password");
	$contact_person = Request::post("contact_person");
	$phone = Request::post("phone");
	$email = Request::post("email");
	$rent_price = Request::post("rent_price");
	$rent_date = Request::post("rent_date");
	$cube_name = Request::post("cube_name");
	$description = Request::post("description");
	$cube_cover = Request::file("cube_cover");
	$is_lock = Request::post("is_lock");
	$is_display = Request::post("is_display");
	
	if (empty($login_name) === true) {
		Session::set("error", "Please enter login name");
	}elseif (empty($email) === true) {
		Session::set("error", "Please enter email");
	}elseif (Util::utf8_string_length($login_name) < 6 && Util::utf8_string_length($login_name) < 20) {
		Session::set("error", "Login name must between 4 and 20 character");
	}elseif (preg_match("/([0-9A-Za-z])+/", $login_name) === false) {
		Session::set("error", "Login name must english & number character");
	}elseif (Util::is_email($email) === false) {
		Session::set("error", "Email format not correct");
	}
	
	$test = Session::get("error");
	
	if (empty($test) === false) {
		exit(Util::redirect(PHP_SELF));
	}
}

// Create & Update & Normal
if ($action == "create") {
	
	if (empty($login_password) === true) {
		Session::set("error", "Please enter login password");
	}else{
	
		$total = Table::count("cubes", array(
			"login_name" => "$login_name"
		));
		
		if ($total > 0) {
			Session::set("error", "Login name already exists");
		}else{
			// Upload Cover
			$resized_image = Upload_Helper::save_image_with_resize(array(
				'input_field' => $cube_cover,
				'save_to_folder' => ATTACHMENT_ROOT."/cube-cover",
				'resize_to_folder' => ATTACHMENT_ROOT."/cube-cover",
				'resize_width' => $config['admin']['cube_cover_image_width'],
				'resize_height' => $config['admin']['cube_cover_image_height'],
			));		

			// Create record
			$table = new Table("cubes");
			$table->login_name = $login_name;
			$table->login_password = md5($login_password);
			$table->contact_person = $contact_person;
			$table->phone = $phone;
			$table->email = $email;
			$table->rent_price = $rent_price;
			$table->rent_date = $rent_date;
			$table->cube_name = $cube_name;
			$table->description = $description;
			$table->cube_cover = $resized_image['file_name_resized'];
			$table->is_lock = $is_lock;
			$table->is_display = $is_display;
			$table->create_date = time();
			$table->insert();
			
			Session::set("success", "New cube was added");
		}
		
	}
	
	Util::redirect(PHP_SELF);

}elseif ($action == "update") {

	$id = Request::post("id");
	
	if (empty($id) === true) {
		Session::set("error", "Not condition provided");
	}elseif (is_numeric($id) === false) {
		Session::set("error", "Condition not correct");
	}else{
		
		// Get exists record's image
		$row = Table::fetch_one_by_column("cubes", $id);
		$old_cube_cover = $row['cube_cover'];

		// Replace exists image else upload it
		$cube_cover_image = File_Helper::update_exists_file(array(
			'old_file_path' => ATTACHMENT_ROOT.'/cube-cover/'.$old_cube_cover,
			'upload_file' => array(
				'input_field' => Request::file('cube_cover'),
				'save_to_folder' => ATTACHMENT_ROOT."/cube-cover",
				'resize_to_folder' => ATTACHMENT_ROOT."/cube-cover",
				'resize_width' => $config['admin']['cube_cover_image_width'],
				'resize_height' => $config['admin']['cube_cover_image_height'],
			),
		));
		
		// Create record
		$table = new Table("cubes", $id);
		
		if (empty($login_password) === false) {
			$table->login_password = md5($login_password);
		}
		
		$table->contact_person = $contact_person;
		$table->phone = $phone;
		$table->email = $email;
		$table->rent_price = $rent_price;
		$table->rent_date = $rent_date;
		$table->cube_name = $cube_name;
		$table->description = $description;
		$table->cube_cover = $cube_cover_image;
		$table->is_lock = $is_lock;
		$table->is_display = $is_display;
		$table->renew();
		
		Session::set("success", "Cube info was updated");
		exit(Util::redirect(Util::make_url(PHP_SELF, array('id' => $id))));
	}

	Util::redirect(PHP_SELF);
	
}else{

	$id = Request::get("id");

	// Edit mode
	if (empty($id) === false && is_numeric($id) === true) {
		// Query member info by cube's id
		$cubes = Table::fetch_one_by_column("cubes", $id);
		
		// Check is or not found out cube
		if (empty($cubes['id']) === true) {
			Session::set("error", "Not found record");
			Util::redirect(PHP_SELF);
		}else{
			$hidden = array(
				array('name' => 'action', 'value' => 'update'),
				array('name' => 'id', 'value' => $id)
			);
			$source = Util::fill_value_for_key(Table::get_columns("cubes"), $cubes);
			
			$page_title = $page_info['page_title']['edit'];
			$remark['password'] = "Please empty it if not change";
			$attributes['input'] = array('readonly' => 'readonly');
			$validation['password'] = "";
			
			$cube_image = ATTACHMENT_URL.'/cube-cover/'.$source['cube_cover'];
		}
	}else{
		// Normal mode
		$hidden = array();
		$source = Util::fill_value_for_key(Table::get_columns("cubes"));
		
		$page_title = $page_info['page_title']['add'];
		$remark['password'] = "";
		$attributes['input'] = array();
		$validation['password'] = "validate[required,length[6,20]]";
		
		$cube_image = "";
	}

	Form::bulid(Form::IS_DETAIL, Form_View::create_detail($page_title, array(
		Form_View::input(
			"Login Name", "login_name", $source['login_name'], 
			"validate[required,length[4,20],custom[noSpecialCaracters]]", "Must be 4~20 english character & number combination", 
			Form_View::TEXT_FIELD, $attributes['input']
		),
		Form_View::password("Password", "login_password", null, $validation['password'], $remark['password']),
		Form_View::input("Contact Person", "contact_person", $source['contact_person']),
		Form_View::input("Phone", "phone", $source['phone']),
		Form_View::input("Email", "email", $source['email']),
		Form_View::input("Rent Price", "rent_price", $source['rent_price']),
		Form_View::input("Rent Date", "rent_date", $source['rent_date'], null, "Y-m-d H:i:s"),
		
		Form_View::input("Cube Name", "cube_name", $source['cube_name']),
		Form_View::textarea("Description", "description", $source['description']),
		Form_View::upload_image("Cube Cover", "cube_cover", $cube_image),
		
		Form_view::select("Lock?", "is_lock", array(
			'N' => "No",
			'Y' => "Yes"
		), $source['is_lock']),
		Form_view::select("Display?", "is_display", array(
			'N' => "No",
			'Y' => "Yes"
		), $source['is_display']),
		
	), $hidden));
	
}
?>