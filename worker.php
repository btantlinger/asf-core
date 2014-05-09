<?php
set_time_limit(0);
error_reporting(E_ERROR | E_PARSE);

if (php_sapi_name() !== 'cli') {
    echo "Worker can only be started from CLI" . PHP_EOL;
    exit(0);
}

if (!isset($argv[1])) {
    echo "No queue specified" . PHP_EOL;
    exit(0);
}
$queue = $argv[1];

//we're running from the cli, so base url does not make sense
define('BASE_URL', "file://" . SRV_ROOT);

//we need to include composer autoload.php here so that auto include is
//available to the cli script
$path = dirname(dirname(dirname(__FILE__))); // should be the vendor path;
$composerAutoloader = $path . DIRECTORY_SEPARATOR . "autoload.php";
if(file_exists($composerAutoloader) && is_file($composerAutoloader)) {
    require $composerAutoloader;
    try {
        \asf\job\Worker::getInstance()->startWorker($queue);
    } catch(\Exception $ex) {
        echo $ex->getMessage() . PHP_EOL;
        exit(0);
    }
} else {    
    exit(0);
}
//EOF