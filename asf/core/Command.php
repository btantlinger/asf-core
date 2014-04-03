<?php namespace asf\core;
/**
 * This class is deprecated and will be removed in a future version.
 *
 * @deprecated since version 1.0
 * @author bob
 */
class Command {

    private static $__instance = NULL;
    private $request;


    private function __construct() {
        
    }

    
    public function setRequest(Request $req) {
        $this->request = $req;
    }

    public function getControllerDirectory() {
        return $this->request->getControllerDirectory();
    }

    public function getControllerName() {
        return $this->request->getControllerName();
    }

    public function getControllerClassName() {
        return $this->request->getControllerClassName();
    }

    public function getControllerFileName() {
        return $this->request->getControllerFileName();
    }

    public function getControllerFilePath() {
        return $this->request->getControllerFilePath();
    }

    public function getFunction() {
        return $this->request->getFunction();
    }

    public function getQueryParams() {
        return $this->request->getQueryParams();
    }

    public function getPostParams() {
        return $this->request->getPostParams();
    }

    public function getReqParams() {
        return $this->request->getReqParams();
    }

    public function getParameters() {
        return $this->request->getParameters();
    }

    public function hasSingleIntParam() {
        return $this->request->hasSingleIntParam();
    }

    public function getControllerBaseUrl() {
        return $this->request->getControllerBaseUrl();
    }

    /**
     * Get the full-qualified namespaced controller class
     */
    public function getControllerClass() {
        return $this->request->getControllerClass();
    }

    public function toUrl($includeParameters = false) {
       return $this->request->toUrl($includeParameters);
    }

    public function currentUrl() {
        return $this->request->currentUrl();
    }

    private function cleanInput(&$arr) {
        return $this->request->cleanInput($arr);
    }

    private function __clone() {
        
    }

    public static function getInstance() {
        if (self::$__instance == NULL) {
            self::$__instance = new Command();
        }
        return self::$__instance;
    }

}

//EOF