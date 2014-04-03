<?php namespace asf\core;
use asf\data\DefaultPagerConfig;
use asf\data\ModelFactory;
use asf\db\DBSingleton;
use asf\session\Session;


/**
 * Description of Controller
 *
 * @author bob
 */
abstract class Controller extends ApplicationController {

    protected $db;   

    public function __construct(Request &$command) {        
        parent::__construct($command);
    }

    /**
     * Loads a model
     *
     * If your model is located in a sub-folder, include the relative path from
     * your models folder. For example blog/Comment
     *
     * @param string $model
     * @return Model The model that was loaded
     */
    protected function loadModel($model, $autoLoadMethods=array()) {
        return ModelFactory::loadModel($model, $autoLoadMethods);
    }

    public function getDatabase() {
        return $this->db;
    }

    public function getTemplate() {
        return $this->tmpl;
    }

    public function getPageTitle() {
        return $this->pageTitle;
    }

    public function setPageTitle($pageTitle) {
        $this->pageTitle = $pageTitle;
        $this->tmpl->pageTitle = & $this->pageTitle;
    }

    public function setVar($key, $value) {
        $this->tmpl->vars[$key] = $value;
    }

    /**
     * Initializes the page
     */
    public function initialize() {        
        Session::start();
        $this->db = DBSingleton::getInstance()->getDatabase();
        parent::initialize();
    }

    public function getTemplateFile() {
        return $this->getView();
    }

    public function setTemplateFile($templateFile) {
        $this->setView($templateFile);
    }

    protected function getSingleIntParam() {
        if ($this->request->hasSingleIntParam()) {
            $params = $this->request->getParameters();
            return $params[0];
        }
        return false;
    }

    protected function createBasicPagerConfig($rpp=40, $lpp=6, $varName="page") {
        return new DefaultPagerConfig($this->request, $rpp, $lpp, $varName);
    }

    protected function destroySession() {
        Session::destroy();
    }
}
//EOF