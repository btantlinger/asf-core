<?php namespace asf\data;
use \asf\core\Request;
/**
 *
 * @author bob
 */
class DefaultPagerConfig implements PagerConfig {

    private $currentPage;
    private $command;
    private $rpp;
    private $lpp;
    private $pageVarName;

    public function __construct(Request &$command, $resultsPerPage=40, $linkPerPage=6, $pageVarName="page") {
        $queryParams = $command->getQueryParams();
        $curPage = 1;
        if (isset($queryParams[$pageVarName])) {
            $curPage = $queryParams[$pageVarName];
        }
        $this->currentPage = $curPage;
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