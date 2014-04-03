<?php namespace asf\data;
/* 
 *  ArrayIterator.php
 *  Created on Jan 2, 2011 3:19:40 AM by bob
 */

/**
 * Description of ArrayIterator
 *
 * @author bob
 */
class IndexedArrayIterator extends AbstractIterator {
    
    protected $dataArray;
    private $counter;
    private $size;

    public function __construct($indexedArray) {
        $this->dataArray = $indexedArray;        
        $this->size = \count($this->dataArray);
        $this->counter = 0;       
    }

    protected function getNextValue() {
        if($this->counter < $this->size) {
            $val = $this->dataArray[$this->counter];
            $this->counter++;
            return $val;
        }
        return false;
    }

    protected function iterationComplete() {
        //nothing to do
    }

    public function size() {
        return $this->size;
    }
}
//EOF