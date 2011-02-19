<?php
require_once dirname(__FILE__).'/kernel/init.php';

$row = $db->fetch_one(sprintf("
	SELECT c.cube_name, c.description AS cube_description, p.cover, p.name, p.price, p.stock, p.description
	FROM %s p
	LEFT JOIN %s c ON p.cube_id = c.id
	WHERE p.id = %d
", Table::table("products"), Table::table("cubes"), Request::get("id", 0)));

include_once View::display('product-detail.html');
?>