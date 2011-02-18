<?php
if (defined("IN_APPS") === false) exit("Access Dead");

$module_url = Module::get_url('page');

Module::create('navigation')->add(array(
	'id' => 'page',
	'group' => 'Page',
	'priority' => 5,
	'items' => array(
		array(
			"text" => "List Page",
			"link" => Util::make_url($module_url."/list.php"),
		)
	)
));
?>