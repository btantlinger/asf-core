<?php namespace asf\cookie;

/*
 *  PHPCookieStore.php
 *  Created on May 5, 2013 10:55:59 PM by bob
 */

/**
 * Description of PHPCookieStore
 *
 * @author bob
 */
class PHPCookieStore implements CookieStore {
    
    public function delete($name) {
        unset($_COOKIE[$name]);
    }

    public function exists($name) {
        return isset($_COOKIE[$name]);
    }

    public function get($name, $default) {
        return (isset($_COOKIE[$name]) ? $_COOKIE[$name] : $default);
    }

    public function isEmpty($name) {
        return empty($_COOKIE[$name]);
    }

    public function set($name, $value, $expiry, $path, $domain) {
        $retval = @setcookie($name, $value, $expiry, $path, $domain);
        if ($retval) {
            $_COOKIE[$name] = $value;
        }
        return $retval;
    }

    public function isCookieSetable() {
        return !headers_sent();
    }
}
//EOF