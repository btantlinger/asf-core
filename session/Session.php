<?php

namespace asf\session;

/*
 *  Session.php
 *  Created on May 2, 2013 5:16:06 PM by bob
 */

/**
 * Static session handling class.  The default session store is the PHPSessionStore
 *
 * @author bob
 */
class Session {

    private static $_sessionStore = null;

    /**
     * Sets the store for the session. Any store currently set will be destroyed.
     * 
     * @param \asf\session\SessionStore $store
     */
    public static function setStore(SessionStore $store) {
        if (self::$_sessionStore !== null) {
            self::$_sessionStore->destroy();
        }

        self::$_sessionStore = $store;
    }

    public static function start() {
        if (self::$_sessionStore === null) {
            self::$_sessionStore = new PHPSessionStore();
        }

        if (!self::$_sessionStore->isStarted()) {
            self::$_sessionStore->start();
        }
    }

    public static function isStarted() {
        if (self::$_sessionStore !== null) {
            return self::$_sessionStore->isStarted();
        }
        return false;
    }

    public static function set($key, $value) {
        if (self::$_sessionStore !== null) {
            self::$_sessionStore->set($key, $value);
        }
    }

    public static function get($key) {
        if (self::$_sessionStore !== null) {
            return self::$_sessionStore->get($key);
        }
        return false;
    }

    public static function remove($key) {
        if (self::$_sessionStore !== null) {
            self::$_sessionStore->remove($key);
        }
    }

    public static function destroy() {
        if (self::$_sessionStore !== null) {
            self::$_sessionStore->destroy();
        }
    }

    public static function getKeys() {
        if (self::$_sessionStore !== null) {
            return self::$_sessionStore->getKeys();
        }
        return array();
    }
}
//EOF