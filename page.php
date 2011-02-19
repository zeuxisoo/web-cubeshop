<?php
require_once dirname(__FILE__).'/kernel/init.php';

$page = Table::fetch_one_by_column("pages", Request::get("type"), "name");

include_once View::display('page.html');
?>