<?php
if (session_status() === PHP_SESSION_NONE) session_start();
define('APP_NAME','CarCare');
define('ROOT_PATH', dirname(__DIR__, 2));
define('APP_PATH', ROOT_PATH.'/app');
define('VIEW_PATH', APP_PATH.'/views');
$script=$_SERVER['SCRIPT_NAME']??''; $baseUrl=rtrim(str_replace('\\','/',dirname($script)),'/');
define('BASE_URL',$baseUrl);
spl_autoload_register(function($c){foreach([APP_PATH."/core/$c.php",APP_PATH."/controllers/$c.php",APP_PATH."/models/$c.php"] as $p){if(file_exists($p)){require_once $p;return;}}});
function view($f){return VIEW_PATH.'/'.ltrim($f,'/');}
function url($p){return BASE_URL.'/'.ltrim($p,'/');}
