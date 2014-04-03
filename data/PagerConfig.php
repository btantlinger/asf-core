<?php namespace asf\data;
/* 
 *  PagerConfig.php
 *  Created on Jan 1, 2011 7:53:27 PM by bob
 */

/**
 *
 * @author bob
 */
interface PagerConfig {
    public function getResultsPerPage();

    public function getLinksPerPage();

    public function getCurrentPage();

    public function getPaginatedUrl($pageNum);
}
//EOF