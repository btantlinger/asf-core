<?php

namespace asf\session;
use asf\core\Config;
use asf\utils\util;

/*
 *  PHPSessionStore.php
 *  Created on May 2, 2013 4:49:53 PM by bob
 */

/**
 * Description of PHPSessionStore
 *
 * @author bob
 */
class PHPSessionStore implements SessionStore {

    public $started = false;
    public function start() {
        if (!$this->isStarted()) {
            $sessProps = Config::getInstance()->getConfig("session");
            SessionManager::sessionInit($sessProps);
            $this->started = true;
        }
    }

    public function destroy() {
        if ($this->isStarted()) {
            SessionManager::regenerateSession();
            $_SESSION = array();
            $this->started = false;
        }
    }

    public function get($key) {
        if (isset($_SESSION[$key])) {
            return $_SESSION[$key];
        } 
        return false;
    }

    public function set($key, $value) {        
        $_SESSION[$key] = $value;
    }

    public function remove($key) {
        unset($_SESSION[$key]);
    }
    
    public function isStarted() {
        return $this->started && SessionManager::isSessionActive();
    }
    
    public function getKeys() {
        return array_keys($_SESSION);
    }
}
//EOF
