<?php namespace asf\db;
use asf\core\Config;
use \PDO;
/*
 *  DBSingleton.php
 *  Created on Apr 3, 2011 3:37:07 PM by bob
 */

/**
 * Description of DBSingleton
 *
 * @author bob
 */
class DBSingleton {

    private static $db;
    private static $__instance = NULL;

    private function __construct() {
        $dbConf = Config::getInstance()->getConfig("database");
        $dsn = "";
        if (isset($dbConf['dsn'])) {
            $dsn = $dbConf['dsn'];
        } else {
            if (!isset($dbConf['engine'])) {
                $dbConf['engine'] = "mysql";
            }
            $dsn = $dbConf['engine'] . ':dbname=' . $dbConf['database'] . ";host=" . $dbConf['server'];
        }

        $options = array(
            //PDO::ATTR_PERSISTENT => true, 
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        );
        self::$db = new Database($dsn, $dbConf['user'], $dbConf['password'], $options);
    }

    private function __clone() {
        
    }

    public static function getInstance() {
        if (self::$__instance == NULL) {
            self::$__instance = new DBSingleton();
        }
        return self::$__instance;
    }

    public function getDatabase() {
        return self::$db;
    }
}
//EOF