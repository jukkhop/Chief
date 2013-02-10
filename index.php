<?php
session_start();
ob_start();
define('AJAX_CALL', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
define('BASE_DIR',  '/'.ltrim('/'.trim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/.').'/', '/'));
if(file_exists('setup.php')) {
	require('setup.php');
	die();
}

require_once('system/config.php');

require_once('system/classes/common.php');
require_once('system/classes/core.php');
require_once('system/classes/controller.php');
require_once('system/classes/database.php');
require_once('system/classes/layout.php');
require_once('system/classes/model.php');
require_once('system/classes/notifications.php');
require_once('system/classes/plugin.php');

date_default_timezone_set(TIMEZONE);

spl_autoload_register(function($module) {
	$module = trim($module, './\\');
	$path = strtolower('modules/'.$module.'/'.$module.'.php');
	if(file_exists($path)) {
		require_once($path);
	}
});

$arguments     = explode('/', trim($_SERVER['QUERY_STRING'], '/'));
$db            = is_null(DB_HOST) ? null : new Database(DB_HOST, DB_USER, DB_PASS, DB_DATABASE);
$layout        = new Layout($db);
$notifications = new Notifications($db);

$module = array_shift($arguments);
$method = array_shift($arguments);

$query = parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY);
parse_str($query, $_GET);

$directory = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));

$module = empty($module) ? DEFAULT_MODULE : $module;
$method = empty($method) ? 'main' : $method;

if(!method_exists($module, $method)) {
	$arguments = array('Page '.$module.'/'.$method.' does not exist.');
	$module = 'error';
	$method = 'main';
}

define('LAYOUT_HEADER', 'layout/header.php');
define('LAYOUT_FOOTER', 'layout/footer.php');

try {
	Core::init($module, $method, $arguments, $db, $layout);
} catch(Exception $e) {
	die($e->getMessage());
}
