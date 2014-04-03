<?php
namespace asf\job;
require_once 'DJJob.php';

/*
 *  Worker.php
 *  Created on Jun 9, 2013 2:10:05 AM by bob
 */
class Worker {

    private static $__instance = NULL;
    private $logger;

    private function __construct() {
        $this->logger = \asf\utils\LogUtil::getLogger(__CLASS__);
        $this->init_job_queue();        
    }

    public function enqueueTask(Task $task, $startWorker=false) {        
        $this->assertValidQueueName($task->getQueueName());
        \DJJob::enqueue($task, $task->getQueueName());
        $this->logger->debug("Enqueued task in queue " . $task->getQueueName());
        if($startWorker) {
            $this->startWorker($task->getQueueName());
        }
    }

    public function batchEnqueueTasks(array $arrayOfTasks, $startWorker=false) {
        $queue_tasks = array();
        foreach ($arrayOfTasks as $task) {
            if ($task instanceof Task) {
                $queue_tasks[$task->getQueueName()][] = $task;
            }
        }
        $this->logger->debug("Batch enqueuing  " . count($queue_tasks) . " tasks");
        
//        foreach($queue_tasks as $q => $qtasks) {
//            \DJJob::bulkEnqueue($qtasks, $q);
//            if($startWorker) {
//                $this->startWorker($q);
//            }
//        }

        foreach ($queue_tasks as $q => $qtasks) {
            if (is_array($qtasks)) {
                foreach ($qtasks as $t) {
                    $this->enqueueTask($t, false);
                }
                if($startWorker) {
                    $this->startWorker($q);
                }
            }
        }
    }

    public function startWorker($queue="default") {   
        $conf = \asf\core\Config::getInstance()->getConfig("worker");        
        if(php_sapi_name() !== 'cli') {            
            //if we are NOT running from cli, then start the shell script
            $workerScript = ASF_PATH . "worker.php";
            if (!file_exists($workerScript)) {
                $this->logger->error("Worker script $workerScript was not found!");
                throw new \Exception("$workerScript was not found!");
            }
            $this->logger->info("Starting worker script: $workerScript");
            
            
            if(!isset($conf['php_cli_cmd'])) {
                $conf['php_cli_cmd'] = "php-cli";
            }
            $cmd = $conf['php_cli_cmd'];
            exec("$cmd $workerScript $queue > /dev/null 2>&1 &");
        } else {
            //we are running from cli, so start a djjob
            $this->logger->info("Starting worker from cli...");
            if(!$this->lockCreate($queue)) {
                $msg = "Unable to create $queue queue lock. Perhaps another queue is already running?";
                $this->logger->error($msg);
                throw new Exception($msg);
            }            
            $this->logger->info("Lock created for $queue");
            
            unset($conf['php_cli_cmd']);
            $defaults = array(
                "count" => 0,
                "sleep" => 5,
                "max_attempts" => 5
            );
            $opts = array_merge($defaults, $conf);
            $opts['queue'] = $queue;
            
            $this->logger->info("Starting DJWorker");
            $worker = new \DJWorker($opts);
            $worker->start();
            $this->logger->info("Worker completed all tasks in queue $queue");
            if(!$this->lockDestroy($queue)) {
                $msg = "Could not destroy lock file for queue $queue!";
                $this->logger->error($msg);
                throw new Exception($msg);
            }
            $this->logger->info("Lock destroyed for $queue");
        }
    }
    

    private function assertValidQueueName($queue) {
        if(!preg_match("/^[A-Za-z0-9_.-]+$/", $queue)) {
            //$q = $task->getQueueName();
            throw new \Exception("Invalid queue name '$queue'. Queue names can only contain alphanumeric chars, dots, dashes, and underscores.");
        }  
    }

    private function init_job_queue() {
        $dbConf = \asf\core\Config::getInstance()->getConfig("worker");
        if(!isset($dbConf['database'])) {            
            $dbConf = \asf\core\Config::getInstance()->getConfig("database");
        } else {
            $dbConf = $dbConf['database']; 
        }       
        
        $dsn = "";
        if (isset($dbConf['dsn'])) {
            $dsn = $dbConf['dsn'];
        } else {
            if (!isset($dbConf['engine'])) {
                $dbConf['engine'] = "mysql";
            }
            $dsn = $dbConf['engine'] . ':dbname=' . $dbConf['database'] . ";host=" . $dbConf['server'];
        }
        
        \DJJob::configure($dsn, array(
            "mysql_user" => $dbConf['user'],
            "mysql_pass" => $dbConf['password'],
        ));
        $this->logger->info("Worker initailized");
    }
    
    private function lockCreate($name) {    
        $lockPath =  "/tmp/asf_" . $name . '_queue.lock';
        $status = @mkdir($lockPath);
        return $status;
    }

    private function lockDestroy($name) {    
        $lockPath =  "/tmp/asf_" . $name . '_queue.lock';
        $status = @rmdir($lockPath);
        return $status;
    }

    public static function getInstance() {
        if (self::$__instance == NULL) {
            self::$__instance = new Worker();
        }
        return self::$__instance;
    }

    private function __clone() {
        
    }
}

