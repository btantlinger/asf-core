<?php namespace asf\data;
use \Exception;

/**
 * Description of SQLResultIterator
 *
 * @author bob
 */
class SQLResultIterator extends AbstractIterator {
    private $result;
    private $page;
    private $db;
    private $resultFreed = false;

    public function __construct(&$sqlResult, &$pageController) {
        $this->result = $sqlResult;
        $this->page = $pageController;
        $this->db = $this->page->getDatabase();
    }

    protected function getNextValue() {
        return $this->db->fetch_array($this->result);         
    }

    protected function iterationComplete() {
        //TODO should the be freed always? What if the list doesn't complete till the end?
        if(!$this->resultFreed) {
            try {
                $this->db->free_result($this->result);
                $this->resultFreed = true;
            } catch(Exception $ex) {}
        }
    }
}
//EOF