
<?php
echo "<pre>\n";
/*
 *  test.php
 *  Created on Apr 2, 2014 3:24:48 PM by bob
 */
require './vendor/autoload.php';
require './asf/App.php';
\asf\App::init();

echo "cwd = " . getcwd() . "\n";

echo "BASE_PATH = " .  BASE_PATH . "\n";
echo "LIB_PATH =  " .  LIB_PATH . "\n";
echo "ASF_PATH =  " .  ASF_PATH . "\n";
echo "APP_PATH =  " .  APP_PATH . "\n";
