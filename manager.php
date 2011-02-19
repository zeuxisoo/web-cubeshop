<?php
require_once dirname(__FILE__).'/kernel/init.php';

$memo_file = CACHE_ROOT."/memo.cgi";

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
		
		$row = Table::fetch_one_by_column("administrators", $username, "username");
		
		if (empty($row['username']) === true) {
			Session::set("error", "Not found user account");
		}elseif ($row['password'] !== md5($password)) {
			Session::set("error", "Password not match");
		}else{
			
			$password = md5($password);
			$auth_key = sha1($username.$password.$config['admin']['cookie_secure_key']);
			$auth_string = Permission_Helper::make_auth("$username\t$password\t$auth_key");

			Util::add_cookie($config['admin']['cookie_auth_name'], $auth_string, time()+$remember);
		}
		
	}
	
	Util::redirect(PHP_SELF);
	
}elseif ($action == "logout") {

	Util::remove_cookie($config['admin']['cookie_auth_name']);
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
			$row = Table::fetch_one_by_column("administrators", $admin['username'], "username");

			if (empty($row['username']) === true) {
				Session::set("error", "Not found user");
			}elseif ($row['password'] != md5($old_password)) {
				Session::set("error", "Old password not match");
			}else{
			
				// Update password
				$table = new Table("administrators", $admin['username'], "username");
				$table->password = md5($new_password);
				$table->renew();
				
				Session::set("success", "Success! Password was updated");
			}
			
		}
		
		Util::redirect(PHP_SELF, array('action' => $action));
		
	}else{
		include_once View::display('admin/change-password.html');
	}

}elseif ($action == "home") {

	if ($option === "update-memo") {
		
		file_put_contents($memo_file, Request::post("memo"));
		
		Session::set("success", "Success! Updated memo");
		Util::redirect(PHP_SELF, array("action" => "home"));
		
	}else{

		$sql_version = $config['db']['driver'] != "text" ? $db->result($db->query("select version()"), 0) : Util::render_error("Not Support");
	
		if (@ini_get("file_uploads")) {
			$max_upload_size = ini_get("upload_max_filesize");
			$max_post_size = ini_get("post_max_size");
		} else {
			$max_post_size = $max_upload_size = '<span class="error">Deny</span>'; 
		}
		
		$is_global_variable = Util::get_php_config("register_globals");
		$is_safe_mode = Util::get_php_config("safe_mode");
		$is_support_gd = Util::get_php_support("imageline");
		
		$database_size = 0;
		if ($config['db']['driver'] != "text") {
			$query = $db->query("SHOW TABLE STATUS LIKE '".$config['db']['prefix']."%'");
		    while ($row = $db->fetch_array($query)) {
				$database_size += $row['Data_length'];
				$database_size += $row['Index_length'];
			}
		}
		
		$database_size = &Util::size_format($database_size);
		$uploaded_file_size = Util::size_format(Util::count_folder_size(ATTACHMENT_ROOT));
		
		$memo = file_get_contents($memo_file);
	
		$info = array(
			'server' => array(
				'time' => date("Y-m-d H:i:s (D)"),
				'sql_version' => $sql_version,
				'upload_detail' => array(
					'max_upload_size' => $max_upload_size,
					'max_post_size' => $max_post_size
				),
				'is_global_variable' => $is_global_variable,
				'is_safe_mode' => $is_safe_mode,
				'is_support_gd' => $is_support_gd
			),
			'system' => array(
				'database_size' => $database_size,
				'uploaded_file_size' => $uploaded_file_size
			)
		);
	
		include_once View::display("admin/index-home.html");
	}

}else{

	if (Util::is_admin() === true) {
		Module::init(array(
			'module_root' => ADMIN_ROOT,
			'module_url' => ADMIN_URL,
		));
	
		$navigations = Module_Navigation::get();

		exit(include_once View::display("admin/panel.html"));
	}

	include_once View::display("admin/index.html");

}
?>