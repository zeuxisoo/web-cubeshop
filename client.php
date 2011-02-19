<?php
require_once dirname(__FILE__).'/kernel/init.php';

if ($action == "login") {

	$username = Request::post("username");
	$password = Request::post("password");
	
	$remember = Request::post("remember");
	$remember = empty($remember) === false ? intval($remember) : 3600*2;

	if (empty($username) === true) {
		Session::set("error", "Please enter username");
	}elseif (empty($password) === true) {
		Session::set("error", "Please enter password");
	}else{
		
		$row = Table::fetch_one_by_column("cubes", $username, "login_name");
		
		if (empty($row['login_name']) === true) {
			Session::set("error", "Not found user account");
		}elseif ($row['login_password'] !== md5($password)) {
			Session::set("error", "Password not match");
		}else{
			
			$password = md5($password);
			$auth_key = sha1($username.$password.$config['client']['cookie_secure_key']);
			$auth_string = Util::make_auth("$username\t$password\t$auth_key");

			Util::add_cookie($config['client']['cookie_auth_name'], $auth_string, time()+$remember);
		}
		
	}
	
	Util::redirect(PHP_SELF);
	
}elseif ($action == "logout") {

	Util::remove_cookie($config['client']['cookie_auth_name']);
	Util::redirect(PHP_SELF);

}elseif ($action == "change-password") {

	Util::need_admin();

	if ($option == "update") {

		$old_password = Request::post("old_password");
		$new_password = Request::post("new_password");
		$confirm_password = Request::post("confirm_password");
	
		if (empty($old_password) === true) {
			Session::set("error", "Please enter old password");
		}elseif (empty($new_password) === true) {
			Session::set("error", "Please enter new password");
		}elseif ($new_password != $confirm_password) {
			Session::set("error", "Confirm password not match");
		}elseif (strlen($new_password) < 6) {
			Session::set("error", "New password can not less than 6 length");
		}else{
			
			// find out exists user by admin's username
			$row = Table::fetch_one_by_column("cubes", $client['username'], "login_name");

			if (empty($row['login_name']) === true) {
				Session::set("error", "Not found user");
			}elseif ($row['login_password'] != md5($old_password)) {
				Session::set("error", "Old password not match");
			}else{
			
				// Update password
				$table = new Table("cubes", $client['username'], "login_name");
				$table->login_password = md5($new_password);
				$table->renew();
				
				Session::set("success", "Success! Password was updated");
			}
			
		}
		
		Util::redirect(PHP_SELF, array('action' => $action));
		
	}else{
		include_once View::display('client/change-password.html');
	}

}elseif ($action == "home") {
	
	$cube = Table::fetch_one_by_column("cubes", $client['username'], "login_name");
	$total_product = Table::count("products", "cube_id = $cube[id]");	
	
	$info['cube'] = array(
		'contact_person' => $cube['contact_person'],
		'phone' => $cube['phone'],
		'email' => $cube['email'],
		'cube_name' => $cube['cube_name'],
		'total_product' => $total_product,
		'create_date' => Util::to_date_time($cube['create_date']),
		'rent_price' => $cube['rent_price']
	);
	
	include_once View::display("client/index-home.html");

}else{

	if (Util::is_client() === true) {
		Module::init(array(
			'module_root' => CLIENT_ROOT,
			'module_url' => CLIENT_URL,
		));
	
		$navigations = Module_Navigation::get();

		exit(include_once View::display("client/panel.html"));
	}

	include_once View::display("client/index.html");

}
?>