<?php namespace asf\data;
/* 
 *  AbstractIterator.php
 *  Created on Jan 2, 2011 3:09:14 AM by bob
 */

/**
 * Description of AbstractIterator
 *
 * @author bob
 */
abstract class AbstractIterator implements ListData {
    private $rowFilter = NULL;    
    private $sizeCache = NULL;
    private $position = 0;    
    private $cache = array();

    public function setRowFilter(RowFilter &$rowFilter) {
        $this->rowFilter = $rowFilter;
    } 
    
    private function getNextAndFilter() {
        $row = $this->getNextValue();
        if($row !== false) {
            if(isset($this->rowFilter) && $this->rowFilter != null) {
                $this->rowFilter->filterRow($row);
             }
        }
        return $row;
    }
    
    public function nextVal() {
        if($this->valid()) {
            $row = $this->current();
            if($row !== false) {
                $this->next();
                return $row;
            }
        }         
        return false;
    }    

    public function current() {
        if(!isset($this->cache[$this->position])) {
            $val =  $this->getNextAndFilter();
            if($val === false) {
                return false;
            }            
            $this->cache[$this->position] = $val;            
        }
        return $this->cache[$this->position]; 
    }

    public function key() {
        return $this->position;
    }

    public function next() {
        $this->position++;
        if(!$this->valid()) {
            $this->iterationComplete();
        }        
    }

    public function rewind() {
        $this->position = 0;
    }

    public function valid() { 
        return $this->current() !== false;
    }
    
    public function size() {
        if($this->sizeCache === NULL) {
            while($row = $this->getNextAndFilter()) {
                $this->cache[] = $row;
            }
            $this->sizeCache = \count($this->cache);
        }
        return $this->sizeCache;
    }

    protected abstract function getNextValue();
    protected abstract function iterationComplete();
}
//EOF