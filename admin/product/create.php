<?php
require_once dirname(dirname(dirname(__FILE__))).'/kernel/init.php';

Permission_Helper::need_admin();

// Define defails
$page_info['page_title']['add'] = "Create Product";
$page_info['page_title']['edit'] = "Edit Product";

// Action Handles
// Share parts
if ($action == "create" || $action == "update") {
	$cube_id = Request::post("cube_id");
	$name = Request::post("name");
	$stock = Request::post("stock");
	$price = Request::post("price");
	$description = Request::post("description");
	$cover = Request::file("cover");
	$is_display = Request::post("is_display");

	if (empty($cube_id) === true) {
		Session::set("error", "Please select cube first");
	}elseif (empty($name) === true) {
		Session::set("error", "Please enter name");
	}elseif ($stock == "") {
		Session::set("error", "Please enter stock");
	}elseif (empty($price) === true) {
		Session::set("error", "Please enter price");
	}
	
	$test = Session::get("error");
	
	if (empty($test) === false) {
		exit(Util::redirect(PHP_SELF));
	}
}

// Create & Update & Normal
if ($action == "create") {
	
	// Upload Cover
	$resized_image = Upload_Helper::save_image_with_resize(array(
		'input_field' => $cover,
		'save_to_folder' => ATTACHMENT_ROOT."/product-cover",
		'resize_to_folder' => ATTACHMENT_ROOT."/product-cover",
		'resize_width' => $config['admin']['product_cover_image_width'],
		'resize_height' => $config['admin']['product_cover_image_height'],
	));		

	// Create record
	$table = new Table("products");
	$table->cube_id = $cube_id;
	$table->name = $name;
	$table->stock = $stock;
	$table->price = $price;
	$table->description = $description;
	$table->cover = $resized_image['file_name_resized'];
	$table->is_display = $is_display;
	$table->create_date = time();
	$table->insert();
	
	Session::set("success", "New product was added");
	
	Util::redirect(PHP_SELF);

}elseif ($action == "update") {

	$id = Request::post("id");
	
	if (empty($id) === true) {
		Session::set("error", "Not condition provided");
	}elseif (is_numeric($id) === false) {
		Session::set("error", "Condition not correct");
	}else{
		
		// Get exists record's image
		$row = Table::fetch_one_by_column("products", $id);
		$old_cover = $row['cover'];

		// Replace exists image else upload it
		$cover_image = File_Helper::update_exists_file(array(
			'old_file_path' => ATTACHMENT_ROOT.'/product-cover/'.$old_cover,
			'upload_file' => array(
				'input_field' => Request::file('cover'),
				'save_to_folder' => ATTACHMENT_ROOT."/product-cover",
				'resize_to_folder' => ATTACHMENT_ROOT."/product-cover",
				'resize_width' => $config['admin']['product_cover_image_width'],
				'resize_height' => $config['admin']['product_cover_image_height'],
			),
		));
		
		// Create record
		$table = new Table("products", $id);
		$table->cube_id = $cube_id;
		$table->name = $name;
		$table->stock = $stock;
		$table->price = $price;
		$table->description = $description;
		$table->is_display = $is_display;
		$table->cover = $cover_image;
		$table->renew();
		
		Session::set("success", "Product info was updated");
		exit(Util::redirect(Util::make_url(PHP_SELF, array('id' => $id))));
	}

	Util::redirect(PHP_SELF);
	
}else{

	$id = Request::get("id");

	// Edit mode
	if (empty($id) === false && is_numeric($id) === true) {
		// Query member info by cube's id
		$cubes = Table::fetch_one_by_column("products", $id);
		
		// Check is or not found out cube
		if (empty($cubes['id']) === true) {
			Session::set("error", "Not found record");
			Util::redirect(PHP_SELF);
		}else{
			$hidden = array(
				array('name' => 'action', 'value' => 'update'),
				array('name' => 'id', 'value' => $id)
			);
			$source = Util::fill_value_for_key(Table::get_columns("products"), $cubes);
			
			$page_title = $page_info['page_title']['edit'];
			$product_image = ATTACHMENT_URL.'/product-cover/'.$source['cover'];
		}
	}else{
		// Normal mode
		$hidden = array();
		$source = Util::fill_value_for_key(Table::get_columns("products"));
		
		$page_title = $page_info['page_title']['add'];
		$product_image = "";
	}
	
	//
	$records = Table::fetch_all("cubes", array("select" => "id, cube_name"));
	$cubes = array();
	foreach($records as $record) {
		$cubes[$record['id']] = $record['cube_name'];
	}

	//
	Form::bulid(Form::IS_DETAIL, Form_View::create_detail($page_title, array(
		Form_View::select("Under Cube", "cube_id", $cubes, $source['cube_id']),
		Form_View::input("Product Name", "name", $source['name']),
		Form_View::input("Product Stock", "stock", $source['stock']),
		Form_View::input("Product Price", "price", $source['price']),
		Form_View::textarea("Description", "description", $source['description']),
		Form_View::upload_image("Product Cover", "cover", $product_image),
		
		Form_view::select("Display?", "is_display", array(
			'N' => "No",
			'Y' => "Yes"
		), $source['is_display']),
	), $hidden));
	
}
?>