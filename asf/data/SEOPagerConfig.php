<?php namespace asf\data;
use \asf\core\Request;
/*
 *  SEOPagerConfig.php
 *  Created on Jul 3, 2011 10:33:50 PM by bob
 */

/**
 * Description of SEOPagerConfig
 *
 * @author bob
 */
class SEOPagerConfig implements PagerConfig {

    private $lastTwoParams;
    private $command;
    
    private $rpp;
    private $lpp;

    function __construct(Request &$command, $rpp=10, $lpp=10) {
        $this->command = $command;
        $this->lastTwoParams = $this->lastTwoElems();
        $this->rpp = $rpp;
        $this->lpp = $lpp;
    }

    public function getCurrentPage() {
        if (!empty($this->lastTwoParams)) {
            return $this->lastTwoParams[1];
        }
        return 1;
    }

    public function getLinksPerPage() {
        return $this->lpp;
    }

    public function getPaginatedUrl($pageNum) {
        $params = $this->command->getParameters();
        $total = count($params);
        if (isset($params[($total - 2)]) && $params[($total - 2)] == 'p') {
            unset($params[($total - 1)]);
            unset($params[($total - 2)]);
        }

        if ($pageNum != 1) {
            $params[] = 'p';
            $params[] = $pageNum;
        }

        $q = $this->command->getQueryParams();
        $queryString = '';
        if (!empty($q)) {
            $queryString .= "?" . http_build_query($this->command->getQueryParams());
        }
        $url = $this->command->toUrl() . implode("/", $params) . "/" . $queryString;
        $url = rtrim($url, '/');
        return $url;
    }

    public function getResultsPerPage() {
        return $this->rpp;
    }

    private function lastTwoElems() {
        $params = $this->command->getParameters();
        $total = count($params);
        if ($total >= 2) {
            $lastTwo = array();
            $lastTwo[0] = $params[($total - 2)];
            $lastTwo[1] = $params[($total - 1)];
            if ($lastTwo[0] == 'p' && ctype_digit($lastTwo[1])) {
                return $lastTwo;
            }
        }
        return false;
    }
}
//EOF