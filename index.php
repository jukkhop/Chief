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

if(!method_exists('Chief\\'.$module, $method)) {
    $arguments = array('Page '.$module.'/'.$method.' does not exist.');
    $module = 'error';
    $method = 'main';
}

define('LAYOUT_HEADER', 'layout/header.php');
define('LAYOUT_FOOTER', 'layout/footer.php');

$layout->setHeader(LAYOUT_HEADER);
$layout->setFooter(LAYOUT_FOOTER);

define('MODULE', $module);
define('METHOD', $method);

require_once('system/init.php');

try {
    Core::init($module, $method, $arguments, $db, $layout);
} catch(Exception $e) {
    die($e->getMessage());
}
