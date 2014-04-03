<?php namespace asf\data;
use asf\utils\PagedResults;
/*
 *  PagedSQLResultSet.php
 *  Created on Jan 5, 2011 4:03:06 PM by bob
 */

/**
 * Description of PagedSQLResultSet
 *
 * @author bob
 */
class PagedSQLResultSet extends SQLResultSet implements Pageable {
    protected $pagerConfig;
    
    private $totalResults;
    private $countSql = NULL;
    private $countParameters = NULL;
    private $totalColumn = "total";
    private $pagingInfo;

    public function __construct(BaseModel &$model, PagerConfig &$pagerConfig = NULL, $sql='', array $params=array(), $countSql=NULL, array $countParams = NULL) {
        parent::__construct($model, $sql, $params);
        $this->countSql = $countSql;
        $this->countParameters = $countParams;
        $this->pagerConfig = $pagerConfig;
    }

    public function setPagerConfig(PagerConfig &$pagerConf) {
        $this->pagerConfig = $pagerConf;
    }

    public function fetchPagingInfo() {
        if (isset($this->pagingInfo)) {
            return $this->pagingInfo;
        }

        if ($this->pagerConfig != NULL) {
            $total = $this->computeTotalResults();
            if ($total !== false) {
                $this->pagingInfo = $this->buildPagingInfo($total);
                return $this->pagingInfo;
            }
        }
        return array();
    }

    public function setCountSQL($countSql, $totalColumn = "total", array $countParameters = NULL) {
        $this->countSql = $countSql;
        $this->totalColumn = $totalColumn;
        $this->countParameters = $countParameters;
    }

    protected function doQuery() {
        $sql = $this->getSQL();
        if (!empty($sql)) {
            $pagingInfo = $this->fetchPagingInfo();
            if (isset($pagingInfo['limit1']) && isset($pagingInfo['limit2'])) {
                $sql .= " LIMIT " . $pagingInfo['limit1'] . ", " . $pagingInfo['limit2'];
            }
            $result = $this->db->prepare($sql);
            $result->execute($this->parameters);
            return new SQLResultIterator($result, $this->model);
        }
        return false;
    }

    protected function computeTotalResults() {
        if (!isset($this->totalResults)) {
            if ($this->countSql == NULL) {
                if ($this->sql == NULL) {
                    return false;
                }
                
                $sql = str_replace(PHP_EOL, " ", $this->sql);
                $pos = stripos($sql, " FROM ");
                if($pos !== false) {                    
                    //group by with a count has a different meaning
                    //TODO - will this work on engines other than mysql?
                    if(preg_match("/\s+GROUP\s+BY\s+/i", $sql)) {
                        $this->countSql = "SELECT COUNT(*) AS {$this->totalColumn} FROM (  $sql  ) AS SQ";
                    } else {
                        $this->countSql = "SELECT COUNT(*) AS {$this->totalColumn} " . substr($sql, $pos, strlen($sql) - $pos);
                    }
                } else {
                    return false;
                }
            }

            $params = !is_null($this->countParameters) ? $this->countParameters : $this->parameters;
            $result = $this->db->prepare($this->countSql);
            $result->execute($params);
            $record = $this->db->fetch_array($result);
            $this->totalResults = $record[$this->totalColumn];
        }
        return $this->totalResults;
    }

    private function buildPagingInfo($totalResults) {
        ////require_once LIB_PATH . 'common/PagedResults.class.php';
        //$curPage = $this->queryParams['page'];
        $paging = new PagedResults();
        $paging->TotalResults = $totalResults;
        $paging->ResultsPerPage = $this->pagerConfig->getResultsPerPage();
        $paging->LinksPerPage = $this->pagerConfig->getLinksPerPage();
        //$paging->PageVarName = "page";
        $paging->CurrentPage = $this->pagerConfig->getCurrentPage();
        $pagingInfo = $paging->InfoArray();

        $pagingInfo["CURRENT_PAGE_URL"] = $this->pagerConfig->getPaginatedUrl($pagingInfo["CURRENT_PAGE"]);
        $pagingInfo["PREV_PAGE_URL"] = $this->pagerConfig->getPaginatedUrl($pagingInfo["PREV_PAGE"]);
        $pageCount = ($pagingInfo["TOTAL_PAGES"]);
        $numUrls = array();
        for ($i = 0; $i < $pageCount; $i++) {
            $numUrls[$i] = $this->pagerConfig->getPaginatedUrl($i + 1);
        }
        $pagingInfo["PAGE_NUMBER_URLS"] = $numUrls;
        $pagingInfo["NEXT_PAGE_URL"] = $this->pagerConfig->getPaginatedUrl($pagingInfo["NEXT_PAGE"]);
        $pagingInfo['limit1'] = & $pagingInfo['MYSQL_LIMIT1'];
        $pagingInfo['limit2'] = & $pagingInfo['MYSQL_LIMIT2'];

        return $pagingInfo;
    }
}
//EOF