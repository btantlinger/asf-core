<?php
namespace asf\job;
/*
 *  Task.php
 *  Created on Jun 9, 2013 2:10:30 AM by bob
 */

/**
 *
 * @author bob
 */
interface Task {
    public function perform();
    public function getQueueName();
}
//EOF