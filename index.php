<?php
namespace Chief;

session_start();
ob_start();

define('AJAX_CALL', isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] == 'XMLHttpRequest');
define('BASE_DIR',  '/'.ltrim('/'.trim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/.').'/', '/'));

if(file_exists('setup.php')) {
    require('setup.php');
    die();
}

require_once('core/common.php');
require_once('core/core.php');
require_once('core/controller.php');
require_once('core/database.php');
require_once('core/exception.php');
require_once('core/layout.php');
require_once('core/model.php');
require_once('core/notifications.php');
require_once('core/plugin.php');

require_once('system/config.php');

date_default_timezone_set(TIMEZONE);
spl_autoload_register(function($module) {
    $module = str_replace('Chief\\', '', $module);
    $module = trim($module, '\\/.');
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

$layout_folder = 'layout/';
$header = 'layout/header.php';
$footer = 'layout/footer.php';

require_once('system/init.php');

if(!method_exists('Chief\\'.$module, $method) && !method_exists('Chief\\'.$module, '__call')) {
    $arguments = array('Page '.$module.'/'.$method.' does not exist.');
    $module = 'error';
    $method = 'main';
}

$layout->setFolder($layout_folder);
$layout->setHeader($header);
$layout->setFooter($footer);

define('MODULE', $module);
define('METHOD', $method);

try {
    Core::init($module, $method, $arguments, $db, $layout);
} catch(Exception $e) {
	Core::init('error', 'main', [$e->getMessage()], $db, $layout);
}

# Save the redirect URL for more stable redirecting
if($module !== 'error') {
	if(isset($_SERVER['REDIRECT_URL'])) {
		$_SESSION['REDIRECT_URL'] = $_SERVER['REDIRECT_URL'];
	} elseif(isset($_SERVER['SCRIPT_URL'])) {
		$_SESSION['REDIRECT_URL'] = $_SERVER['SCRIPT_URL'];
	} else {
		$_SESSION['REDIRECT_URL'] = BASE_DIR;
	}
}
