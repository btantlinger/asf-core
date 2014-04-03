<?php namespace asf\data;
//use \asf\core\Command;
use \asf\core\Request;
/**
 * Description of BasicPagerConfig
 *
 * @author bob
 * @deprecated since 1.0 use defaultpagerconfig instead
 */
class BasicPagerConfig implements PagerConfig {

    private $currentPage;
    private $command;
    private $rpp;
    private $lpp;
    private $pageVarName;

    public function __construct($curPageNum, Request &$command, $resultsPerPage=40, $linkPerPage=6, $pageVarName="page") {
        $this->currentPage = $curPageNum;
        $this->command = $command;
        $this->rpp = $resultsPerPage;
        $this->lpp = $linkPerPage;
        $this->pageVarName = $pageVarName;
    }

    public function getCurrentPage() {
        return $this->currentPage;
    }

    public function getLinksPerPage() {
        return $this->lpp;
    }

    public function getResultsPerPage() {
        return $this->rpp;
    }

    public function getPaginatedUrl($pageNum) {
        $pageNum = $pageNum == NULL ? 1 : $pageNum;
        $queryParams = $this->command->getQueryParams();
        $queryParams[$this->pageVarName] = $pageNum;
        $para = '?';
        if (is_array($queryParams)) {
            $para .= http_build_query($queryParams);
        }
        $url = rtrim($this->command->toUrl(true), "/");
        return $url . $para;
    }

    public function setResultsPerPage($rpp) {
        $this->rpp = $rpp;
    }

    public function setLinksPerPage($lpp) {
        $this->lpp = $lpp;
    }
}
//EOF