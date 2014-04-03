<?php namespace asf\core;

//use asf\utils;
/*
 *  Config.php
 *  Created on Aug 19, 2011 3:35:21 PM by bob
 */

/**
 * Description of Config
 *
 * @author bob
 */
class Config {

    private $conf_exts = array(
        ASF_ENV_DEVELOPMENT => ASF_ENV_DEVELOPMENT,        
        ASF_ENV_TESTING => ASF_ENV_TESTING,
        ASF_ENV_PRODUCTION => "inc"
    );
    private static $__instance = NULL;
    private $configArray = array();

    public function __construct() {        
        $env = getenv(ASF_ENV_VAR);
        $ext = ($env !== false && isset($this->conf_exts[$env])) ? 
                $this->conf_exts[$env] : $this->conf_exts[ASF_ENV_PRODUCTION]; 
        
        $incFile = APP_PATH . "config.$ext.php";
        if(file_exists($incFile) && is_file($incFile)) {
            require_once APP_PATH . "config.$ext.php";
            if(isset($config) && is_array($config)) {
                $this->configArray = $config; //getConfigArray();
            }
        }
    }

    private function __clone() {
        
    }

    public static function getInstance() {
        if (self::$__instance == NULL) {
            self::$__instance = new Config();
        }
        return self::$__instance;
    }

    public function getConfig($key) {
        if (isset($this->configArray[$key])) {
            if (is_array($this->configArray[$key])) {
                $val = $this->configArray[$key]; //return copy so we can't modify original        
                return $val;
            }
        }
        return array();
    }

    public function getConfigArray() {
        $arr = $this->configArray;
        return $arr;
    }
    
    public function getEnv() {
        $env = getenv(ASF_ENV_VAR);
        if($env !== false && isset($this->conf_exts[$env])) {
            return $env;
        }
        return ASF_ENV_PRODUCTION;
    }
    
    public function getEnvConfigFilePrefix() {
        return $this->conf_exts[$this->getEnv()];
    }
}
//EOF