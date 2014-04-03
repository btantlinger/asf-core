<?php
namespace asf;

class App {    
    private static $is_initalized = false;
    
    public static function init() { 
        if(!defined("ASF_ENV_VAR")) {
            include_once 'bootstrap.php';
        }
        self::$is_initalized = true;
    }
    
    public static function is_initialized() {
        return self::$is_initalized;
    }    
}
//EOF