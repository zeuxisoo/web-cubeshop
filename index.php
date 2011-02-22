<?php
require_once dirname(__FILE__).'/kernel/init.php';

$cubes = Table::fetch_all("cubes", array(
	"select" => "id, cube_name, cube_cover",
	"order" => "ORDER BY create_date DESC",
	"where" => array(
		"is_display" => "Y"
	)
));

include_once View::display('index.html');
?>