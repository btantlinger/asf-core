<?php
namespace asf\data;
/*
 *  ModelObserver.php
 *  Created on Nov 20, 2013 1:24:36 AM by bob
 */

/**
 *
 * @author bob
 */
interface ModelObserver { 
    
    public function onBeforeAdd($modelData);
    
    public function onAfterAdd($id);
    
    public function onBeforeUpdate($modelData);
    
    public function onAfterUpdate($id);
    
    public function onBeforeDelete($modelData);
    
    public function onAfterDelete($id);    
}

