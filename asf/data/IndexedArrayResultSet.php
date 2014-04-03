<?php namespace asf\data;

/*
 *  IndexedArrayResultSet.php
 *  Created on Jul 17, 2011 4:36:04 PM by bob
 */
class IndexedArrayResultSet implements ResultSet {
    
    private $dataArray;

    public function __construct($dataArray) {      
        $this->dataArray = $dataArray;
    }
    
    public function fetchData() {
        return new IndexedArrayIterator($this->dataArray);  
    }

    public function toArray() {
        return $this->dataArray;
    }

    public function getIterator() {
        return $this->fetchData();
    }
}
//EOF