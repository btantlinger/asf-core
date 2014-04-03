<?php
namespace asf\data;
/*
 *  ModelEventManager.php
 *  Created on Nov 20, 2013 1:20:55 AM by bob
 */

/**
 * Description of ModelEventManager
 *
 * @author bob
 */
class ModelEventManager {

    private static $observers = array();    
    
    public static function addObserver($modeClass, ModelObserver $observer) {
        $modeClass = ltrim($modeClass, "\\");
        if(!isset(self::$observers[$modeClass])) {
            self::$observers[$modeClass] = array();
        }
        self::$observers[$modeClass][] = $observer;
    }
    
    
    public static function removeObserver($modelClass, ModelObserver $observer) {
        $modelClass = ltrim($modelClass, "\\");
        if(!empty(self::$observers[$modelClass])) {
            $obs = self::$observers[$modelClass];
            $n = count($obs);
            for($i = 0; $i < $n; $i++) {
                if($obs[$i] === $observer) {
                    unset($obs[$i]);
                }
            }
            self::$observers[$modelClass] = array_values($obs);
        }
    }
    
    public static function fireBeforeAdd($modelClass, $modelData) {
        $obs = self::getObservers($modelClass);
        foreach($obs as $o) {
            $modelData = $o->onBeforeAdd($modelData);
        }
        return $modelData;
    }
    
     public static function fireBeforeUpdate($modelClass, $modelData) {
        $obs = self::getObservers($modelClass);
        foreach($obs as $o) {
            $modelData = $o->onBeforeUpdate($modelData);
        }
        return $modelData;
    }
    
    public static function fireBeforeDelete($modelClass, $modelData) {
        $obs = self::getObservers($modelClass);
        foreach($obs as $o) {
            $modelData = $o->onBeforeDelete($modelData);
        }
        return $modelData;
    }
    
    public static function fireAfterAdd($modelClass, $pk) {
        $obs = self::getObservers($modelClass);
        foreach($obs as $o) {
            $o->onAfterAdd($pk);
        }
    }
    
     public static function fireAfterUpdate($modelClass, $pk) {
        $obs = self::getObservers($modelClass);
        foreach($obs as $o) {
            $o->onAfterUpdate($pk);
        }
    }
    
    public static function fireAfterDelete($modelClass, $pk) {
        $obs = self::getObservers($modelClass);
        foreach($obs as $o) {
            $o->onAfterDelete($pk);
        }
    }
    
    private static function getObservers($modelClass) {
        return empty(self::$observers[$modelClass]) ? array() : self::$observers[$modelClass];
    }
}
