<?php namespace asf\session;

/*
 *  SessionMessages.php
 *  Created on Aug 22, 2011 2:40:07 PM by bob
 */

/**
 * Description of SessionMessages
 *
 * @author bob
 */
class SessionMessages {
    private static $__instance = NULL;

    private function __construct() {
        Session::start();       
    }

    public static function getInstance() {
        if (self::$__instance == NULL) {
            self::$__instance = new SessionMessages();
        }
        return self::$__instance;
    }
    
    public function addSuccessMessage($msg) {
        $this->addSessionMessage($msg, "success");
    }

    public function addInfoMessage($msg) {
        $this->addSessionMessage($msg, "info");
    }

    public function addErrorMessage($msg) {
        $this->addSessionMessage($msg, "error");
    }

    public function addWarningMessage($msg) {
        $this->addSessionMessage($msg, "warning");
    }

    public function setFlashData($data, $enc=true) {
        if ($enc) {
            $data = \asf\utils\util::sanitize_recursive($data);
        }
        Session::set("FLASH_DATA", $data);
    }

    public function getFlashData() {
        return $this->getData("FLASH_DATA");
    }

    public function clearAllMessages() {
        Session::remove("MSG_ARR");
        Session::remove("WARN_MSG_ARR");
        Session::remove("ERR_MSG_ARR");
        Session::remove("SUCCESS_MSG_ARR");
        Session::remove("FLASH_DATA");
    }

    /**
     * Adds a session message
     *
     * @param string $msg
     * @param string $type either info, warning, or error
     */
    public function addSessionMessage($msg, $type="info") {
        if ($type == "info") {
            $this->addMsg("MSG_ARR", $msg);
        } else if ($type == "warning") {
             $this->addMsg("WARN_MSG_ARR", $msg);
        } else if ($type == 'error') {
             $this->addMsg("ERR_MSG_ARR", $msg);
        } else if($type == 'success') {
            $this->addMsg("SUCCESS_MSG_ARR", $msg);
        }
    }

    public function getErrorMessages() {
        return $this->getData("ERR_MSG_ARR");
    }
    
    public function getSuccessMessages() {
        return $this->getData("SUCCESS_MSG_ARR");
    }

    public function getWarningMessages() {
        return $this->getData("WARN_MSG_ARR");
    }

    public function getInfoMessages() {
        return $this->getData("MSG_ARR");
    }
    
    private function getData($key) {
        $d = Session::get($key);
        if($d) {
            return $d;
        }
        return array();
    }
    
    private function addMsg($key, $msg) {
        $arr = Session::get($key);
        if($arr === false) {
            $arr = array();
        }
        if(is_array($arr)) {
            $arr[] = $msg;            
            Session::set($key, $arr);
        }
    }
}
//EOF