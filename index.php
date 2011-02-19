<?php
require_once dirname(__FILE__).'/kernel/init.php';

$cubes = Table::fetch_all("cubes", array(
	"select" => "id, cube_name, cube_cover",
	"order" => "ORDER BY create_date DESC"
));

include_once View::display('index.html');
?>