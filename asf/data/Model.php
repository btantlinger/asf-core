<?php namespace asf\data;

use \Exception;
use \PDO;

/**
 * 
 * @deprecated since version 1.0
 */
abstract class Model extends BaseModel {

    private $sqlUtils;
    private $useTransactions = true;
    
    public function __construct() {
        parent::__construct();
        $this->sqlUtils = new sqlUtil($this->db);
    }

    public function isUseTransactions() {
        return $this->useTransactions;
    }


    /**
     * Specify whether the methods startTransaction, endTransaction, and rollback should be enabled.
     *
     * If this value is false these methods do nothing
     *
     * @param bool $useTransactions
     */
    public function setUseTransactions($useTransactions) {
        $this->useTransactions = $useTransactions;
    }
    
    
    /**
     * Begins a transaction
     */
    protected function startTransaction() {
        if ($this->useTransactions === true) {
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_SILENT); 
            $this->db->begin();
            
        }
    }

    /**
     * Ends a transaction by either committing or rolling back if an error occured
     */
    protected function endTransaction($rollbackMsg=NULL) {
        if ($this->useTransactions === true) {
            if ($this->db->has_error()) {
                $this->rollback($rollbackMsg);
            } else {                
                try {                    
                    $this->db->commit();
                    $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
                } catch(Exception $ex) {
                    $msg = $rollbackMsg != NULL ? $rollbackMsg : $ex->getMessage();
                    $this->rollback($rollbackMsg);
                }
            }
        }
    }

    /**
     * Rolls back a transacation and throws an exception with the specified errMsg
     * @param string $errMsg
     */
    protected function rollback($errMsg=NULL) {
        if ($this->useTransactions === true) {
            //try {
                $this->db->rollback();
            //} catch(Exception $ex){};
            if ($errMsg == NULL) {
                $errMsg = "An error has occurred. The operation cannot be performed.";
            }
            $this->db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); 
            throw new Exception($errMsg);
        }
    }    

    protected function getOrderByClauseFromParameters($params, $default='') {
        return $this->sqlUtils->getOrderByClauseFromParameters($params, $default);
    }

    protected function getLimitClauseFromParameters($params) {
        return $this->sqlUtils->getLimitClauseFromParameters($params);
    }

    /**
     * adds an SQL condition to the conditionsArray if and only if it appears in the parametersArray
     *
     * E.g  name = 'Bob Smith'
     *
     * @param array $conditionsArray
     * @param array $parametersArray
     * @param array $key  The array key
     */
    protected function addCondition(&$conditionsArray, &$parametersArray, $key, $tableName='', $operator='=') {
        $this->sqlUtils->addCondition($conditionsArray, $parametersArray, $key, $tableName, $operator);
    }

    protected function addNullableCondition(&$conditionsArray, &$parametersArray, $key, $tableName='') {
        $this->sqlUtils->addNullableCondition($conditionsArray, $parametersArray, $key, $tableName);
    }

    /**
     * Adds an SQL condition to the conditionsArray for between numeric values.  The parameter MUST be
     * formatted such as 100 - 200 (note space before and after '-'), and MUST contains numeric values.
     *
     * @param array $conditionsArray
     * @param array $parametersArray
     * @param array $key  The array key
     * @param string $tableName  the name of the table
     */
    protected function addNumericBetweenCondition(&$conditionsArray, &$parametersArray, $key, $tableName='') {
        $this->sqlUtils->addNumericBetweenCondition($conditionsArray, $parametersArray, $key, $tableName);
    }

    protected function addLikeTextCondition(&$conditionsArray, &$parametersArray, $key, $tableName='') {
        $this->sqlUtils->addLikeTextCondition($conditionsArray, $parametersArray, $key, $tableName);
    }

    protected function addIdInCondition(&$conditionsArray, &$parametersArray, $key='ids', $tableName='', $idField='id') {
        $this->sqlUtils->addIdInCondition($conditionsArray, $parametersArray, $key, $tableName, $idField);
    }

    protected function addContainsTextCondition(&$conditionsArray, $textFragment, $dbTextColsArray) {
        $this->sqlUtils->addContainsTextCondition($conditionsArray, $textFragment, $dbTextColsArray);
    }

    protected function addStartsWithCondition(&$conditionsArray, $textFragment, $dbTextColsArray) {
        $this->sqlUtils->addStartsWithCondition($conditionsArray, $textFragment, $dbTextColsArray);
    }

    //TODO document this method
    /**
     *
     *
     *
     * @param array $paramsArr  the query params
     * @param string $type the type, eg. quotes, calls, contacts
     * @param string $dateField the date field in the table
     */
    protected function addDateRangeCondition(&$conditionsArray, &$paramsArr, $dbDateField, $key="dated_by") {
       $this->sqlUtils->addDateRangeCondition($conditionsArray, $paramsArr, $dbDateField, $key);
    }

    protected function doBasicInsert($options, $tableName, $idField='id') {
        $this->assertRequired($this->getRequiredFields(), $options);
        $data = $this->allowed($this->getFields(), $options);
        unset($data[$idField]);
        $this->startTransaction();
        $id = $this->db->query_insert($tableName, $data);
        $this->endTransaction();
        return $id;
    }

    protected function doBasicUpdate($options, $tableName, $idField='id') {
        $this->assertRequired(array($idField), $options);
        $data = $this->allowed($this->getFields(), $options);
        $id = $this->db->escape($data[$idField]);
        unset($data[$idField]);
        $this->startTransaction();
        $this->db->query_update($tableName, $data, "$idField = '$id'");
        $this->endTransaction();
    }

    protected function doBasicDelete($options, $tableName, $idField='id') {
        $this->assertRequired(array($idField), $options);
        $id = $this->db->escape($options[$idField]);
        $this->startTransaction();
        $this->db->query("DELETE FROM $tableName WHERE $idField = '$id'");
        $this->endTransaction();
    }

    protected function createList($sql, RowFilter $rowFilter=NULL) {
        $sqlResultSet = new SQLResultSet($this);
        $sqlResultSet->setRowFilter($rowFilter);
        $sqlResultSet->setSQL($sql);
        return $sqlResultSet;
    }

    protected function createPagedList($sql, $countSql, RowFilter $rowFilter=NULL, PagerConfig $pagerConfig=NULL) {
        $sqlResultSet = new PagedSQLResultSet($this, $pagerConfig);
        $sqlResultSet->setCountSQL($countSql);
        $sqlResultSet->setRowFilter($rowFilter);
        $sqlResultSet->setSQL($sql);
        return $sqlResultSet;    
        
    }
    
    /**
     * @deprecated since version 1.0 Use assertRequired instead
     * @param type $required
     * @param type $data
     * @param type $msg
     */
    
    protected function checkRequired($required, $data, $msg=NULL) {
        $this->assertRequired($required, $data, $msg);
    }

    protected function basicSQLQuery($options, $tableName, $countOnly=false) {
        if ($countOnly) {
            $sql = "SELECT COUNT(*) AS total FROM $tableName ";
        } else {
            $sql = "SELECT * FROM $tableName ";
        }

        $conds = array();
        foreach ($this->getFields() as $fieldName) {
            $this->addCondition($conds, $options, $fieldName, $tableName);
        }

        $where = implode(" AND ", $conds);
        if (!empty($where)) {
            $sql .= " WHERE $where ";
        }

        if (!$countOnly) {
            $sql .= $this->getOrderByClauseFromParameters($options, "ORDER BY $tableName.id DESC");
        }
        return $sql;
    } 
}
//EOF