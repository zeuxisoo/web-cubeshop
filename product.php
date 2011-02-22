<?php
require_once dirname(__FILE__).'/kernel/init.php';

$products = Table::fetch_all("products", array(
	"select" => "id, name, cover",
	"order" => "ORDER BY create_date DESC",
	"where" => array(
		"is_display" => "Y"
	)
));

include_once View::display('product.html');
?>