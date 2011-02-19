<?php
error_reporting(E_ALL);
header("content-Type: text/html; charset=UTF-8");

if (version_compare(PHP_VERSION, '6.0.0', '<') === true) {
	@set_magic_quotes_runtime(0);
}

define('IN_APPS', true);
define('KERNEL_ROOT', str_replace('\\', '/', dirname(__FILE__)));
define('WWW_ROOT', dirname(KERNEL_ROOT));
define('CACHE_ROOT', dirname(KERNEL_ROOT).'/cache');
define('TEMPLATE_ROOT', WWW_ROOT.'/template');

require_once KERNEL_ROOT."/config.php";
require_once KERNEL_ROOT."/common.php";

define('SITE_URL', $config['init']['site_url']);
define('ATTACHMENT_ROOT', dirname(KERNEL_ROOT).'/'.$config['init']['attachment']['folder']);
define('ATTACHMENT_URL', SITE_URL.'/'.$config['init']['attachment']['folder']);
define('PHP_SELF', Util::get_php_self());
define('SCRIPT_URI', Util::get_php_uri());
define('ADMIN_ROOT', dirname(KERNEL_ROOT).'/'.$config['admin']['folder']);
define('ADMIN_URL', SITE_URL.'/'.$config['admin']['folder']);
define('CLIENT_ROOT', dirname(KERNEL_ROOT).'/'.$config['client']['folder']);
define('CLIENT_URL', SITE_URL.'/'.$config['client']['folder']);

$_GET = Util::auto_quote($_GET);
$_POST = Util::auto_quote($_POST);
$_COOKIE = Util::auto_quote($_COOKIE);
$_REQUEST = Util::auto_quote($_REQUEST);

foreach(array('_COOKIE', '_POST', '_GET') as $_request) {
    foreach($$_request as $_key => $_value) {
        $_key{0} != '_' && $$_key = Util::auto_quote($_value);
    }
}
unset($_request, $_key, $_value, $_request);

if (function_exists("date_default_timezone_set")) {
	date_default_timezone_set($config['init']['timezone']);
}

if ($config['init']['no_cache'] === true) {
	header("Cache-Control: no-cache, must-revalidate, max-age=0");
	header("Expires: 0");
	header("Pragma:	no-cache");
	header("Content-Type: text/html; charset=utf-8");
}

if ($config['init']['show_php_error'] === true) {
	error_reporting(E_ALL);
}else{
	error_reporting(E_ALL & ~E_NOTICE);
}
Session::init();

View::set_settings(array(
	"debug" => $config['init']['show_view_error'],
	"view_folder" => TEMPLATE_ROOT,
	"view_cache_folder" => CACHE_ROOT."/template",
	"theme" => "default",
));

$db = Database::create($config['db']);

Table::init($db);

ob_get_clean(); ob_start('ob_gzhandler');

$admin = Util::fill_value_for_key(array("username", "password", "auth_key"));
$admin_auth = Request::cookie($config['admin']['cookie_auth_name']);
if (isset($admin_auth) === true && empty($admin_auth) === false) {
	list($admin_username, $admin_password, $admin_auth_key) = explode("\t", Permission_Helper::make_auth($admin_auth, "DECODE"));
	$admin = array(
		'username' => $admin_username,
		'password' => $admin_password,
		'auth_key' => $admin_auth_key
	);
	unset($admin_username, $admin_password, $admin_auth_key);
}
unset($admin_auth);

$client = Util::fill_value_for_key(array("username", "password", "auth_key"));
$client_auth = Request::cookie($config['client']['cookie_auth_name']);
if (isset($client_auth) === true && empty($client_auth) === false) {
	list($client_username, $client_password, $client_auth_key) = explode("\t", Permission_Helper::make_auth($client_auth, "DECODE"));
	$client = array(
		'username' => $client_username,
		'password' => $client_password,
		'auth_key' => $client_auth_key
	);
	
	$row = Table::fetch_one_by_column("cubes", $client_username, "login_name");
	$client['id'] = $row['id'];
	
	// If logged in admin mode will not trigger it (god mode?), else redirect to exists
	if ($row['is_lock'] == "Y" && Util::is_admin() === false) {
		Util::remove_cookie($config['client']['cookie_auth_name']);
		Util::redirect($config['client']['login_page']);
	}
	
	unset($client_username, $client_password, $client_auth_key, $row);
}
unset($admin_auth);

$action = Request::get("action");
$action = empty($action) === true ? Request::post("action") : $action;

$option = Request::get("option");
$option = empty($option) === true ? Request::post("option") : $option;

$flow_message['error'] = Session::get("error", true);
$flow_message['success'] = Session::get("success", true);
?>