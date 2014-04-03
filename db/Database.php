<?php namespace asf\db;
use \PDO;
use \PDOStatement;

/*
 *  PDODatabase.php
 *  Created on Feb 24, 2013 12:03:47 AM by bob
 */

/**
 * Description of PDODatabase
 *
 * @author bob
 */
class Database extends PDO {
    
    /**
     * Keeps a cache of the column names for a table
     * 
     * @var array 
     */
    protected $columnNameCache = array();

    /**
     * Stores the transaction nesting level.
     *
     * @var int
     */
    protected $transactionNestingLevel = 0;

    /**
     * This flag is set to true when an SQL query has failed.
     * In this case the transaction should be rolled back.
     *
     * @var bool
     */
    protected $transactionErrorFlag = false;

    /**
     * Begins a transaction.
     *
     * This method executes a begin transaction query unless a
     * transaction has already been started (transaction nesting level > 0 )
     *
     * Each call to beginTransaction() must have a corresponding commit() or
     * rollback() call.
     *
     * @see commit()
     * @see rollback()
     * @return bool
     */
    public function beginTransaction() {
        $retval = true;
        if ($this->transactionNestingLevel == 0) {
            $retval = parent::beginTransaction();
        }
        // else NOP

        $this->transactionNestingLevel++;
        return $retval;
    }

    /**
     * Commits a transaction.
     *
     * If this this call to commit corresponds to the outermost call to
     * beginTransaction() and all queries within this transaction were
     * successful, a commit query is executed. If one of the queries returned
     * with an error, a rollback query is executed instead.
     *
     * This method returns true if the transaction was successful. If the
     * transaction failed and rollback was called, false is returned.
     *
     * @see beginTransaction()
     * @see rollback()
     * @return bool
     */
    public function commit() {
        if ($this->transactionNestingLevel <= 0) {
            $this->transactionNestingLevel = 0;
            throw new DbTransactionException("commit() called before beginTransaction().");
        }

        $retval = true;
        if ($this->transactionNestingLevel == 1) {
            if ($this->transactionErrorFlag) {
                parent::rollback();
                $this->transactionErrorFlag = false; // reset error flag
                $retval = false;
            } else {
                parent::commit();
            }
        }
        // else NOP

        $this->transactionNestingLevel--;
        return $retval;
    }

    /**
     * Rollback a transaction.
     *
     * If this this call to rollback corresponds to the outermost call to
     * beginTransaction(), a rollback query is executed. If this is an inner
     * transaction (nesting level > 1) the error flag is set, leaving the
     * rollback to the outermost transaction.
     *
     * This method always returns true.
     *
     * @see beginTransaction()
     * @see commit()
     * @return bool
     */
    public function rollback() {
        if ($this->transactionNestingLevel <= 0) {
            $this->transactionNestingLevel = 0;
            throw new DbTransactionException("rollback() called without previous beginTransaction().");
        }

        if ($this->transactionNestingLevel == 1) {
            parent::rollback();
            $this->transactionErrorFlag = false; // reset error flag
        } else {
            // set the error flag, so that if there is outermost commit
            // then ROLLBACK will be done instead of COMMIT
            $this->transactionErrorFlag = true;
        }

        $this->transactionNestingLevel--;
        return true;
    }
    
    
    /**
     * End a transaction.
     * 
     * A call to this method will result in one of three possible outcomes.
     * 1. An open transaction will be successfully commited
     * 2. A dbTransactionException will be thrown.
     * 
     * The exception is thrown if this method is called before beginTransaction() 
     * or if a previously open transaction at the current
     * nested transaction level has allready been rolled back.
     * 
     */
    public function endTransaction() {
        if(!$this->commit()) {
            throw new DbTransactionException("The current transaction has been rolled back.");
        }
    }
    

    /**
     * Checks if a transaction is open.  Does essentially the same thing as
     * inTransaction, but should work on PHP < 5.3.3 where inTransaction does not exist
     * @return bool
     */
    public function isInTransaction() {
        if (method_exists($this, "inTransaction")) {
            return $this->inTransaction();
        }
        return $this->transactionNestingLevel > 0;
    }

    public function fetch_all_array($sql) {
        $result = $this->query($sql);
        $out = array();
        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            $out[] = $row;
        }
        $result->closeCursor();
        return $out;
    }

    public function fetch_array(PDOStatement &$result) {
        return $result->fetch(PDO::FETCH_ASSOC);
    }

    public function free_result(PDOStatement &$result) {
        $result->closeCursor();
    }

    public function has_error() {
        $error = $this->errorInfo();
        if ($error[2] !== null) {
            return true;
        }
        return false;
    }

    public function query_first($query_string) {
        $query_id = $this->query($query_string);
        $out = $this->fetch_array($query_id);
        $this->free_result($query_id);
        return $out;
    }

    /**
     * 
     * @param type $table
     * @param array $data
     * @return boolean the last insertedId, or FALSE on failure
     */
    public function query_insert($table, array $data) {
        $q = "INSERT INTO `" . $table . "` ";
        $v = '';
        $n = '';
        $values = array();

        foreach ($data as $key => $val) {
            $n.="`$key`, ";
            if (strtolower($val) == 'null') {
                $v.="NULL, ";
            } else if (strtolower($val) == 'now()') {
                $v.="NOW(), ";
            } else {
                $v.= "?, ";
                $values[] = $val;
            }
        }

        $q .= "(" . rtrim($n, ', ') . ") VALUES (" . rtrim($v, ', ') . ");";
        $stmt = $this->prepare($q);
        if ($stmt !== false) {
            if ($stmt->execute($values)) {
                return $this->lastInsertId();
            }
        }

        return false;
    }

    /**
     * 
     * @param string $table
     * @param array $data
     * @param string $where
     * @return boolean  the number of rows affected or FALSE on failure
     */
    public function query_update($table, array $data, $where = '1') {
        $q = "UPDATE `" . $table . "` SET ";
        $values = array();
        foreach ($data as $key => $val) {
            if (strtolower($val) == 'null') {
                $q.= "`$key` = NULL, ";
            } else if (strtolower($val) == 'now()') {
                $q.= "`$key` = NOW(), ";
            } else {
                $q.= "`$key`= ?, ";
                $values[] = $val;
            }
        }

        $q = rtrim($q, ', ') . ' WHERE ' . $where . ';';
        $stmt = $this->prepare($q);
        if ($stmt !== false) {
            if ($stmt->execute($values)) {
                //if($stmt->rowCount() > 0) 
                {
                    return $stmt->rowCount();
                }
            }
        }
        return false;
    }

    public function getColumnNames($table, $useCache=true) {        
        if($useCache && isset($this->columnNameCache[$table])) {
            return $this->columnNameCache[$table];
        }
        
        $driver = $this->getAttribute(PDO::ATTR_DRIVER_NAME);
        $table = $this->escape($table);
        if ($driver == 'sqlite') {
            $sql = "PRAGMA table_info('" . $table . "');";
            $key = "name";
        } else if ($driver == 'mysql') {
            $sql = "DESCRIBE " . $table . ";";
            $key = "Field";
        } else {
            $sql = "SELECT column_name FROM information_schema.columns WHERE table_name = '" . $table . "';";
            $key = "column_name";
        }

        $list = $this->fetch_all_array($sql);
        $fields = array();
        foreach ($list as $record) {
            $fields[] = $record[$key];
        }
        $this->columnNameCache[$table] = $fields;
        return $fields;
    }

    // these methods are all deprecated, they are only here for backwards compatibility from a previous DB abstraction layer.

    /**
     * Is db connected
     * 
     * Here only for backwards compatability, since PDO is always connected
     * this method is redundant
     * 
     * @deprecated since version 1.0
     * @return boolean
     */
    public function is_connected() {
        return true;
    }

    /**
     * escapes a value such as mysql_real_escape_string
     * 
     * This can be dangerous, do not use. It's here for backwards compat only. 
     * 
     * @deprecated since version 1.0
     * @param type $string
     */
    public function escape($string) {
        if (get_magic_quotes_runtime()) {
            $string = stripslashes($string);
        }
        return substr($this->quote($string), 1, -1);
    }

    /**
     * Does nothing. Has no analog in PDO. Do not use.
     * 
     * @deprecated since version 1.0
     */
    public function affected_rows() {
        
    }

    /**
     * Begins a transaction.  Simply calls beginTransaction. This method is deprecated, use beginTransaction instead
     * 
     * @deprecated since version 1.0
     */
    public function begin() {
        return $this->beginTransaction();
    }

    /**
     * Deprecated close method does nothing. instead use:
     * 
     * $db = null;
     * 
     * @deprecated since version 1.0
     */
    public function close() {
        
    }

    /**
     * Deprecated connect method, does nothing, has no analog in PDO.
     * @deprecated since version 1.0
     * @param type $new_link
     */
    public function connect($new_link = false) {
        
    }
}
//EOF