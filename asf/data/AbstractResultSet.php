<?php namespace asf\data;
/*
 *  AbstractResultSet.php
 *  Created on Jan 1, 2011 5:10:35 PM by bob
 */

/**
 * Description of AbstractResultSet
 *
 * @author bob
 */
abstract class AbstractResultSet implements ResultSet {

    private $rowFilter = NULL;

    public function __construct() {

    }

    public function setRowFilter(&$rowFilter) {
        $this->rowFilter = $rowFilter;
    }

    public function fetchData() {
        $iterator = $this->doQuery();
        if ($iterator !== false) {
            if ($this->rowFilter != NULL) {
                $iterator->setRowFilter($this->rowFilter);
            }
            return $iterator;
        }

        return new NullIterator();
    }

    public function getIterator() {
        return $this->fetchData();
    }

    public function toArray() {
        $data = array();
        $list = $this->fetchData();
        while ($row = $list->nextVal()) {
            $data[] = $row;
        }
        return $data;
    }

    protected abstract function doQuery();
}
//EOF