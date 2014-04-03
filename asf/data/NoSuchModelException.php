<?php namespace asf\data;
/*
 *  NoSuchModelException.php
 *  Created on Mar 2, 2013 2:56:19 AM by bob
 */

/**
 * Description of NoSuchModelException
 *
 * @author bob
 */
class NoSuchModelException extends \Exception {
    public function __construct($msg) {
        parent::__construct($msg);
    }
}
//EOF