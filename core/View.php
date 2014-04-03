<?php namespace asf\core;
/**
 * Description of View
 *
 * @author bob
 */
class View {
    private static $__instance = NULL;
    private static $view;

    private function __construct() {
        require_once ASF_PATH . 'savant/Savant3.php';
        self::$view = new \Savant3();
        self::$view->addPath("template", VIEWS_PATH);
        self::$view->addPath("template", ERROR_VIEWS_PATH);
    }

    private function __clone() {
        
    }

    public static function getInstance() {
        if (self::$__instance == NULL) {
            self::$__instance = new View();
        }
        return self::$__instance;
    }

    public function getView() {
       return self::$view;
    }
}
//EOF