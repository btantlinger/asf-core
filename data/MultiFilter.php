<?php namespace asf\data;
/* 
 *  MultiFilter.php
 *  Created on Dec 30, 2010 3:08:21 PM by bob
 */
class MultiFilter implements RowFilter {
    private $filters = array();

    public function addFilter(RowFilter &$filter) {
        if(!is_null($filter)) {
            $this->filters[] = $filter;
        }
    }

    public function filterRow(&$row) {
        foreach($this->filters as $filter) {
            $filter->filterRow($row);
        }
        return $row;
    }
}
//EOF