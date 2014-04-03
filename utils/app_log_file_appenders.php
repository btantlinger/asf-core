<?php
/*
 *  log4phpAppenders.php
 *  Created on Jun 17, 2013 1:28:38 PM by bob
 */
class AppLoggerAppenderFile extends \LoggerAppenderFile {
    public function setFile($file) {
        $path = APP_PATH . "logs/$file";
        parent::setFile($path);
    } 
}

class AppLoggerAppenderRollingFile extends \LoggerAppenderRollingFile {
    public function setFile($file) {
        $path = APP_PATH . "logs/$file";
        parent::setFile($path);
    }
}

class AppLoggerAppenderDailyFile extends \LoggerAppenderDailyFile {
    public function setFile($file) {
        $path = APP_PATH . "logs/$file";
        parent::setFile($path);
    }
}