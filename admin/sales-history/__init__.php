<?php
if (defined("IN_APPS") === false) exit("Access Dead");

$module_url = Module::get_url('sales-history');

Module::create('navigation')->add(array(
	'id' => 'sales-history',
	'group' => 'Sales History',
	'items' => array(
		array(
			"text" => "Add Sales History",
			"link" => Util::make_url($module_url."/create.php"),
		),
		array(
			"text" => "List Sales History",
			"link" => Util::make_url($module_url."/list.php"),
		),
		array(
			"text" => "Search Sales History",
			"link" => Util::make_url($module_url."/search.php"),
		),
	)
));
?>