<?php namespace asf\core;
use asf\session\Session;
use asf\session\SessionMessages;
use asf\utils\LogUtil;

/**
 * Processes a request, and dispatches it to the correct controller.
 *
 * @author bob
 */
class Router {

    private $requestURI;
    private $logger;
    
    public function __construct($requestURI = null) {        
        if(!\asf\App::is_initialized()) {
            \asf\App::init();
        }        
        $this->logger = LogUtil::getLogger(__CLASS__);
        $this->requestURI = $requestURI === null ? $_SERVER['REQUEST_URI'] : $requestURI;
        $this->logger->debug("Routing initialized for request {$this->requestURI}");
    }

    public function dispatch() {       
        $request = new Request($this->requestURI);
        
        //TODO  - remove
        Command::getInstance()->setRequest($request);
        
        $view = View::getInstance()->getView();
        $view->request =& $request;
        $path = $request->getControllerFilePath();
        $controllerClass = $request->getControllerClass();
        $refCls = new \ReflectionClass($controllerClass);
        
        if (\is_file($path) && $refCls->isSubclassOf("\asf\core\ApplicationController") && $refCls->isInstantiable()) {
            try {                
                $controller = new $controllerClass($request);
                $controller->initialize();
                $controller->execute();
                $controller->show();
                $this->logger->debug("Showing view for request {$this->requestURI} - " . $controller->getView()) ;
            } catch (\Exception $ex) {
                $this->logger->error("Error occured in controller: {$this->requestURI}", $ex);
                \header("HTTP/1.0 500 Internal Server Error");
                $view->errorMessage = $ex->getMessage();
                $view->exception = $ex;
                $view->display("500.tpl.php");                
            }
        } else {
            $this->logger->debug("404 File not found for request {$this->requestURI}");
            \header("HTTP/1.0 404 Not Found");
            $view->errorMessage = "404 File Not Found";
            $view->display("404.tpl.php");            
        }
        
        if(Session::isStarted()) {
            SessionMessages::getInstance()->clearAllMessages();
            $this->logger->debug("Flash data cleared");
        }
    }
}
//EOF