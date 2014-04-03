<?php namespace asf\data;

/*
 *  TreeSQLResultSet.php
 *  Created on Jan 5, 2011 4:53:48 PM by bob
 */

/**
 * Description of TreeSQLResultSet
 *
 * @author bob
 */
class TreeSQLResultSet extends SQLResultSet implements TreeData {

    private $idCol;
    private $parentIdCol;

    public function __construct(BaseModel &$model, $idCol='id', $parentIdCol='parent_id') {
        parent::__construct($model);
        $this->idCol = $idCol;
        $this->parentIdCol = $parentIdCol;
    }

    public function toTree() {
        $refs = array();
        $list = array();

        $iterator = $this->fetchData();
        while ($row = $iterator->nextVal()) {
            $thisref = &$refs[$row[$this->idCol]];
            $thisref[$this->parentIdCol] = $row[$this->parentIdCol];
            $thisref['row'] = $row;

            if ($row[$this->parentIdCol] == 0) {
                $list[$row[$this->idCol]] = &$thisref;
            } else {
                $refs[$row[$this->parentIdCol]]['children'][$row[$this->idCol]] = &$thisref;
            }
        }

        return $list;
    }
}
//EOF