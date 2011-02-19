<?php
require_once dirname(__FILE__).'/kernel/init.php';

$id = Request::get("id", 0);

if (empty($id) === true) {

	Util::redirect(SITE_URL."/index.php");

}else{

	$cube = Table::fetch_one_by_column("cubes", $id);
	
	$products = Table::fetch_all("products", array(
		"select" => "id, name, cover",
		"order" => "ORDER BY create_date DESC",
		"where" => array(
			"cube_id" => $id
		)
	));
	
	include_once View::display('cube.html');
	
}
?>