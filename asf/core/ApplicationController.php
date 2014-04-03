<?php namespace asf\core;
use asf\session\Session;
use asf\session\SessionMessages;
use asf\utils\util;
/*
 *  BaseController.php
 *  Created on Mar 2, 2013 8:52:53 PM by bob
 */

/**
 * Description of BaseController
 *
 * @author bob
 */
abstract class ApplicationController {

    /**
     * @deprecated since version 1.0
     * @var Request
     */
    protected $command; //this is only here for backwards compatability
    protected $request;
    protected $queryParams;
    protected $postParams;
    protected $reqParams;
    protected $tmpl;
    protected $view;
    protected $config;
    private $initialized = false;
    private $templateFile;

    function __construct(Request &$request) {
        $this->request = $request;
        $this->command =& $this->request;
        $this->queryParams = $this->request->getQueryParams();
        $this->postParams = $this->request->getPostParams();
        $this->reqParams = $this->request->getReqParams();

        $this->view = View::getInstance()->getView();
        $this->view->addPath("template", VIEWS_PATH . $this->request->getControllerDirectory());
        $this->tmpl =& $this->view; //backwards compat

        $this->view->baseUrl = BASE_URL;
        $this->view->queryParams = & $this->queryParams;
        $this->view->ctrlUrl = $this->request->toUrl();
        $this->view->ctrlDefaultUrl = $this->request->getControllerBaseUrl();
        $this->view->ctrlName = $this->request->getControllerName();
        $this->view->currentUrl = $this->request->toUrl(true);
        $this->view->currentUrlWithQuery = $this->request->currentUrl();
    }

    /**
     * Called after the controller is instantiated
     *
     * Subclasses can override this to do custom initialization
     */
    protected function onInit() {
        
    }

    /**
     * Called when no other function parameter is supplied
     *
     * Subclasses should override this
     */
    public function _default() {
        
    }

    public function getView() {
        return $this->templateFile;
    }

    public function setView($templateFile) {
        if(!empty($templateFile)) {
            if (!util::ends_with($templateFile, ".tpl.php")) {
                $templateFile = ".tpl.php";
            }
        }
        $this->templateFile = $templateFile;
    }

    public function initialize() {
        if (!$this->initialized) {
            $this->config = Config::getInstance()->getConfigArray();
            $this->onInit();
            $this->initialized = true;
        }
    }

    public function isInitialized() {
        return $this->initialized;
    }

    public function show() {
        if (Session::isStarted()) {
            //\asf\utils\util::var_dump_die($_SESSION);
            $this->view->infoMessages = SessionMessages::getInstance()->getInfoMessages();
            $this->view->warningMessages = SessionMessages::getInstance()->getWarningMessages();
            $this->view->errorMessages = SessionMessages::getInstance()->getErrorMessages();
            $this->view->successMessages = SessionMessages::getInstance()->getSuccessMessages();
            $this->view->flashData = SessionMessages::getInstance()->getFlashData();
        } else {
            $this->view->infoMessages = array();
            $this->view->warningMessages = array();
            $this->view->errorMessages = array();
            $this->view->successMessages = array();
            $this->view->flashData = array();
        }
        if (!empty($this->templateFile)) {
            $this->view->display($this->templateFile);
        }
    }

    public function execute() {
        $functionToCall = $this->request->getFunction();
        if ($this->request->getFunction() == '') {
            $functionToCall = 'default';
        }

        if (!\is_callable(array(&$this, '_' . $functionToCall))) {
            $functionToCall = 'error';
        }

        $this->view->ctrlFunction = $functionToCall;
        $this->view->ctrlName = $this->request->getControllerName();
        $this->view->isDefault = ($functionToCall == 'default') ? true : false;
        \call_user_func(array(&$this, '_' . $functionToCall));
    }

    /**
     * Called when an invalid url is supplied to this controller.
     * 
     * Default behavior shows a 404 page as defined in errors/404.tpl.php.
     *
     * Subclasses can override this.
     *
     */
    public function _error() {
        $this->display404();
    }
    
    protected function display404() {
        \header("HTTP/1.0 404 Not Found");
        $this->view->errorMessage = "404 File Not Found";
        $this->setView("404.tpl.php");
    }

    public function redirectTo($path) {        
        if(util::starts_with($path, "http://") || util::starts_with($path, "https://")) {
            \header("location: " . $path);
        } else {
            $url = \rtrim(BASE_URL, "/");
            if (!\asf\utils\util::starts_with($path, "/")) {
                $path = "/" . $path;
            }
            \header("location: " . $url . $path);
        }
        die();
    }

    protected function initializeSession() {
        Session::start();        
    }

    protected function addSessionMessage($msg, $type = "info") {
        SessionMessages::getInstance()->addSessionMessage($msg, $type);
    }

    protected function addSessionSuccessMessage($msg) {
        SessionMessages::getInstance()->addSessionMessage($msg, "success");
    }

    protected function addSessionInfoMessage($msg) {
        SessionMessages::getInstance()->addSessionMessage($msg, "info");
    }

    protected function addSessionErrorMessage($msg) {
        SessionMessages::getInstance()->addSessionMessage($msg, "error");
    }

    protected function addSessionWarningMessage($msg) {
        SessionMessages::getInstance()->addSessionMessage($msg, "warning");
    }

    protected function setFlashData($data, $enc = true) {
        SessionMessages::getInstance()->setFlashData($data, $enc);
    }

    protected function getFlashData() {
        return SessionMessages::getInstance()->getFlashData();
    }
}
//EOF