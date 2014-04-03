<?php namespace asf\session;

/*
 *  SessionStore.php
 *  Created on May 2, 2013 4:43:55 PM by bob
 */

/**
 *
 * @author bob
 */
interface SessionStore {
    
    public function start();
    
    public function set($key, $value);
    
    public function get($key);
    
    public function remove($key);
    
    public function destroy();
    
    public function isStarted();
    
    public function getKeys();
}
//EOF