<?php
if (defined("IN_APPS") === false) exit("Access Dead");

$module_url = Module::get_url('cube');

Module::create('navigation')->add(array(
	'id' => 'cube',
	'group' => 'Cube',
	'items' => array(
		array(
			"text" => "Add Cube",
			"link" => Util::make_url($module_url."/create.php"),
		),
		array(
			"text" => "List Cube",
			"link" => Util::make_url($module_url."/list.php"),
		)
	)
));
?>