<?php namespace asf\data;
/* 
 *  NullRowFilter.php
 *  Created on Jan 2, 2011 6:06:12 AM by bob
 */

/**
 * Description of NullRowFilter
 *
 * @author bob
 */
class NullRowFilter implements RowFilter {
    public function filterRow(&$row) {
        //do nothing
    }
}
//EOF