<?php
$config['init']['site_name'] = 'CubeShop';
$config['init']['site_description'] = 'Simple and simple Web-based CubeShop';
$config['init']['site_url'] = 'http://localhost/git/cubeshop';
$config['init']['timezone'] = 'Asia/Hong_Kong';
$config['init']['no_cache'] = true;
$config['init']['show_php_error'] = true;
$config['init']['show_view_error'] = true;
$config['init']['attachment']['folder'] = "attachment";

$config['db']['driver']   = "mysql";
$config['db']['host']  	  = "localhost"; 
$config['db']['username'] = "root";
$config['db']['password'] = "root";
$config['db']['database'] = "project_cubeshop";
$config['db']['charset']  = 'utf-8';
$config['db']['port']     = "3306";
$config['db']['prefix']   = "pc_";
$config['db']['debug']    = true;

$config['admin']['folder'] = "admin";
$config['admin']['cookie_auth_name'] = "cubeshop_admin_auth";
$config['admin']['cookie_secure_key'] = "ABC";
$config['admin']['login_page'] = 'manager.php';
$config['admin']['cube_cover_image_width'] = 200;
$config['admin']['cube_cover_image_height'] = 200;
$config['admin']['product_cover_image_width'] = 200;
$config['admin']['product_cover_image_height'] = 200;

$config['client']['folder'] = "client";
$config['client']['cookie_auth_name'] = "cubeshop_client_auth";
$config['client']['cookie_secure_key'] = "DEF";
$config['client']['login_page'] = 'client.php';
?>