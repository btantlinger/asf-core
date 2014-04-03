<?php namespace asf\data;
/* 
 *  Pageable.php
 *  Created on Jan 5, 2011 4:01:20 PM by bob
 */

/**
 *
 * @author bob
 */
interface Pageable {
    /**
     * @return array  the paging info array
     */
    public function fetchPagingInfo();
}
//EOF