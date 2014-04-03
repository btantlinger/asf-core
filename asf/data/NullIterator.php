<?php namespace asf\data;

/**
 * 
 *
 * @author bob
 */
class NullIterator implements ListData  {
    public function nextVal() {
        return false;
    }

    public function size() {
        return 0;
    }

    public function current() {
        return NULL;
    }

    public function key() {
        return NULL;
    }

    public function next() {
        
    }

    public function rewind() {
        
    }

    public function valid() {
        return false;
    }
}
//EOF