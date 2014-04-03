<?php
/*
 *  bootstrap.php
 *  Created on Apr 1, 2013 11:42:13 PM by bob
 */
define("ASF_ENV_PRODUCTION", "prod");
define("ASF_ENV_DEVELOPMENT", "devel");
define("ASF_ENV_TESTING", "test");
define("ASF_ENV_VAR", "ASF_ENV");

$sep = DIRECTORY_SEPARATOR;
if(!defined("APP_NAMESPACE")) {
    define("APP_NAMESPACE", "app"); 
}

if(!defined("ASF_PATH")) {
    define("ASF_PATH", asf_dir());
}

if(!defined("LIB_PATH")) {
    define("LIB_PATH", dirname(ASF_PATH) . $sep);
}

if(!defined("BASE_PATH")) {
    define("BASE_PATH", getcwd() . $sep);
}

if(!defined("SRV_ROOT")) {
    define("SRV_ROOT", BASE_PATH);
}

if(!defined("APP_BASE_PATH")) {
    define('APP_BASE_PATH', BASE_PATH);
}

define('APP_PATH', APP_BASE_PATH . str_replace('\\', $sep, APP_NAMESPACE) . $sep);
define('WEB_ROOT_PATH', SRV_ROOT);
define('VIEWS_PATH', APP_PATH . "views" . $sep);
define('ERROR_VIEWS_PATH',  APP_PATH . "errors" . $sep);
//define('CONFIG_PATH', APP_PATH . "config.inc.php");
define('MODELS_PATH', APP_PATH . "models" . $sep);
define('CONTROLLERS_PATH', APP_PATH . "controllers" . $sep);
define('UTILS_PATH', APP_PATH . "utils" . $sep);
define("APP_LIB_PATH", APP_PATH . "lib" . $sep);
unset($sep);

//require LIB_PATH . "vendor/autoload.php";
require ASF_PATH . 'SplClassLoader.php';
$asfLoader = new \asf\SplClassLoader("asf", LIB_PATH);
$asfLoader->register();

$asfAppLoader = new \asf\SplClassLoader(APP_NAMESPACE, APP_BASE_PATH);
$asfAppLoader->register();

//setup logging
\asf\utils\LogUtil::initLogging();

asf_clean_input();
@include UTILS_PATH . 'utils.php';


/**
 * Removes magic quotes from input, if they are enabled.
 */
function asf_clean_input() {
    //strip slashes from user input
    if (get_magic_quotes_gpc()) {
        $process = array(&$_GET, &$_POST, &$_COOKIE, &$_REQUEST);
        while (list($key, $val) = each($process)) {
            foreach ($val as $k => $v) {
                unset($process[$key][$k]);
                if (is_array($v)) {
                    $process[$key][stripslashes($k)] = $v;
                    $process[] = &$process[$key][stripslashes($k)];
                } else {
                    $process[$key][stripslashes($k)] = stripslashes($v);
                }
            }
        }
        unset($process);
    }
}

function asf_dir() {
    if (DIRECTORY_SEPARATOR == '/') {
        return dirname(__FILE__) . '/';
    }
    return str_replace('\\', '/', dirname(__FILE__)) . DIRECTORY_SEPARATOR;
}
//EOF