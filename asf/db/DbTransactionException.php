<?php namespace asf\db;
use \Exception;
/*
 *  DbTransactionException.php
 *  Created on Mar 2, 2013 2:10:28 AM by bob
 */

/**
 * Description of DbTransactionException
 *
 * @author bob
 */
class DbTransactionException extends Exception {
    public function __construct($msg, Exception $prev=NULL) {
        parent::__construct($msg, 0, $prev);
    }
}
//EOF