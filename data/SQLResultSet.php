<?php namespace asf\data;
/* 
 *  SQLResultSet.php
 *  Created on Jan 1, 2011 5:57:58 PM by bob
 */


/**
 * Description of SQLResultSet
 *
 * @author bob
 */
class SQLResultSet extends AbstractResultSet {
    protected $sql;
    protected $parameters = array();
    protected $db;
    protected $model;

    public function __construct(BaseModel &$model, $sql='', array $parameters=array()) {
        $this->db = $model->getDatabase();
        $this->model = $model;
        $this->sql = $sql;
        $this->parameters = $parameters;
    }

    protected function doQuery() {
        if(!empty($this->sql)) {
            $result = $this->db->prepare($this->sql);
            $result->execute($this->parameters);
            return new SQLResultIterator($result, $this->model);
        }
        
        return false;
    }

    public function setSQL($sql, array $parameters = array()) {
        $this->sql = $sql;
        $this->parameters = $parameters;
    }

    public function getSQL() {
        return $this->sql;
    }
}
//EOF