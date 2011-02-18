<?php
if (defined("IN_APPS") === false) exit("Access Dead");

$module_url = Module::get_url('product');

Module::create('navigation')->add(array(
	'id' => 'product',
	'group' => 'Product',
	'items' => array(
		array(
			"text" => "Add Product",
			"link" => Util::make_url($module_url."/create.php"),
		),
		array(
			"text" => "List Product",
			"link" => Util::make_url($module_url."/list.php"),
		),
		array(
			"text" => "Search Product",
			"link" => Util::make_url($module_url."/search.php"),
		),
	)
));
?>