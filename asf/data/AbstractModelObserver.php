<?php
namespace asf\data;
/*
 *  AbstractModelObserver.php
 *  Created on Nov 20, 2013 1:57:09 AM by bob
 */

/**
 * Description of AbstractModelObserver
 *
 * @author bob
 */
class AbstractModelObserver implements ModelObserver {
    
    public function onAfterAdd($id) {
        
    }

    public function onAfterDelete($id) {
        
    }

    public function onAfterUpdate($id) {
        
    }

    public function onBeforeAdd($modelData) {
        return $modelData;
    }

    public function onBeforeDelete($modelData) {
        return $modelData;
    }

    public function onBeforeUpdate($modelData) {
        return $modelData;
    }    
}
