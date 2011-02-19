<?php
require_once dirname(__FILE__)."/kernel/init.php";

$products = Table::fetch_all("products", array(
	"select" => "id, name, cover",
	"order" => "ORDER BY RAND(), create_date DESC"
));

include_once View::display('random.html');
?>