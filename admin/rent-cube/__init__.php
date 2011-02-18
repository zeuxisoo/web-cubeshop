<?php
if (defined("IN_APPS") === false) exit("Access Dead");

$module_url = Module::get_url('rent-cube');

Module::create('navigation')->add(array(
	'id' => 'rent-cube',
	'group' => 'Rent Cube',
	'items' => array(
		array(
			"text" => "List Rent Cube",
			"link" => Util::make_url($module_url."/list.php"),
		),
		array(
			"text" => "Search Rent Cube",
			"link" => Util::make_url($module_url."/search.php"),
		),
	)
));
?>