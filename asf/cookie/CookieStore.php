<?php namespace asf\cookie;

/*
 *  CookieStore.php
 *  Created on May 5, 2013 10:51:30 PM by bob
 */

/**
 *
 * @author bob
 */
interface CookieStore {
    
    public function isCookieSetable();

    public function exists($name);
    
    public function isEmpty($name);
    
    public function get($name, $default);
    
    public function set($name, $value, $expiry, $path, $domain);
    
    public function delete($name);
}
//EOF
