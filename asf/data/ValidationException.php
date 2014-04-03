<?php namespace asf\data;
/*
 *  ValidationException.php
 *  Created on Mar 2, 2013 2:38:00 AM by bob
 */

/**
 * An exception indicating that some condition is not valid
 *
 * 
 * @author bob
 */
class ValidationException extends \Exception {
    private $errorMessages = array();
    
    /**
     * 
     * @param string $msg default message
     * @param array $errorMsgs  an associative array of error messages where the key is the field and the value is the error message
     */
    public function __construct($msg, array $errorMsgs) {
        parent::__construct($msg);
        //parent::__construct($errorMsgs[0]);
        $this->errorMessages = $errorMsgs;
    }
    
    /**
     * Get the invalid fields
     * @return array
     */
    public function getFields() {
        return array_keys($this->errorMessages);
    }
    
    /**
     * Get error messages
     * @return array
     */
    public function getMessages() {
        return array_values($this->errorMessages);
    }
    
    
    /**
     * Get the an associative array of fields/error messages
     * @return array
     */
    public function getValidationErrors() {
        return $this->errorMessages;
    }
}
//EOF
