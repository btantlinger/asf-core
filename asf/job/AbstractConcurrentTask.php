<?php
namespace asf\job;
/*
 *  AbstractConcurrentTask.php
 *  Created on Jun 9, 2013 7:09:25 AM by bob
 */
abstract class AbstractConcurrentTask implements Task {
    
    private $queueName = null;
    private $queueNamePrefix;
    private $maxConcurrent = 1;    
    
    function __construct($queueNamePrefix, $maxConcurrent) {
        $this->queueNamePrefix = $queueNamePrefix;
        $this->maxConcurrent = $maxConcurrent;
    }
    
    public function getQueueName() {
        if($this->queueName === null) {
            $max = intval($this->maxConcurrent);
            $max = ($max >= 1) ? $max : 1;        
            $this->queueName = $this->queueNamePrefix . rand(1, $max);
        }
        return $this->queueName;
    }
    
    public function getQueueNamePrefix() {
        return $this->queueNamePrefix;
    }

    public function getMaxConcurrent() {
        return $this->maxConcurrent;
    }

    public function setMaxConcurrent($maxConcurrent) {
        $this->maxConcurrent = $maxConcurrent;
    }    
}
