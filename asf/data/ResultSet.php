<?php namespace asf\data;
/* 
 *  ResultSet.php
 *  Created on Jan 1, 2011 5:05:01 PM by bob
 */

/**
 *
 * A set of results
 *
 * @author bob
 */
interface ResultSet extends \IteratorAggregate {

    /**
     * @return ListData the iterator of data
     */
    public function fetchData();

    /**
     * Iterate over the data and return an array
     * 
     * @return a prefetched data array of the iteratable data
     *
     */
    public function toArray();

}
//EOF