<?php
namespace asf\utils;

/*
 *  LogUtil.php
 *  Created on Jun 17, 2013 12:51:16 PM by bob
 */

/**
 * Description of LogUtil
 *
 * @author bob
 */
class LogUtil {
    public static function getLogger($loggerName, $convertSlashesToDots = true) {
        if ($convertSlashesToDots) {
            $loggerName = ltrim($loggerName, '\\');
            $loggerName = str_replace('\\', '.', $loggerName);
        }
        return \Logger::getLogger($loggerName);
    }

    public static function initLogging() {  
        require_once 'app_log_file_appenders.php';
        $logConf = APP_PATH . "logger." . \asf\core\Config::getInstance()->getEnvConfigFilePrefix() . ".xml";
        if (is_readable($logConf) && is_file($logConf)) {
            \Logger::configure($logConf);
        }
    }
}
