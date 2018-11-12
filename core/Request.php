<?php namespace asf\core;

/*
 *  Request.php
 *  Created on Apr 2, 2013 12:17:51 PM by bob
 */

/**
 * Description of Request
 *
 * @author bob
 */
class Request {

    private $directory = '';
    private $name = '';
    private $function = '';
    private $parameters = array();
    private $queryParams;
    private $postParams;
    private $reqParams = array();

    public function __construct($requestURI = NULL) {
        
        if($requestURI === NULL) {
            $requestURI = $_SERVER['REQUEST_URI'];
        }
        
        //$requestURI = $requestURI == NULL ? $_SERVER['REQUEST_URI'] : $requestURI;
        $requestURI = \asf\utils\util::end_slash($requestURI);
        $pos = \asf\utils\util::last_index_of($requestURI, '?');
        if ($pos !== false) {
            $requestURI = substr($requestURI, 0, $pos);
        }

        $requestURI = explode('/', $requestURI);
        $bpath = array_values(array_diff(explode(DIRECTORY_SEPARATOR, BASE_PATH), array('')));
        $req = array_values(array_diff($requestURI, array('')));

        $droot = array(); //array_intersect($bpath, $req);
        for ($i = count($bpath) - 1, $j = 0; $i >= 0; $i--, $j++) {
            if (isset($req[$j]) && ($bpath[$i] == $req[$j])) {
                $droot[] = $req[$j];
            } else {
                break;
            }
        }

        $commandArray = array();
        $numParts = count($req);
        for ($i = 0; $i < $numParts; $i++) {
            if (isset($droot[$i]) && $req[$i] == $droot[$i]) {
                continue;
            }
            $commandArray[] = $req[$i];
        }

        if (end($commandArray) == "index.php") {
            unset($commandArray[count($commandArray) - 1]);
        }

        $commandArray = array_diff($commandArray, array('')); //get rid of empties
       
        $controllerDir = '';
        $controllerName = '';
        $controllerFunct = '';
        $parameters = array();
        $phpFile = '';

        //TODO only allows for one level of subdirs...
        $path = CONTROLLERS_PATH;
        if (empty($commandArray[0])) {
            $controllerName = "root";
        } else {
            $head = $commandArray[0];
            $path = \asf\utils\util::end_slash(CONTROLLERS_PATH);
            $phpFile = $this->ctrlFileName($head);
            //echo "looking for: " , $path . $phpFile . "<br>";
            if (is_file($path . $phpFile)) {
                //echo "Found file $phpFile in $path<br>";
                $controllerName = $head;
                $commandArray = array_splice($commandArray, 1);
            } else if (is_dir($path . $commandArray[0])) {
                $path .= \asf\utils\util::end_slash($commandArray[0]);
                $controllerDir = $commandArray[0];

                if (empty($commandArray[1])) {
                    //echo "should use Root for subdir $path<br>";
                    $controllerName = 'root';
                    $commandArray = array_splice($commandArray, 1);
                } else {
                    $head = $commandArray[1];
                    $phpFile = $this->ctrlFileName($head);
                    $f = $path . $phpFile;
                    //echo "looking for $f<br>";
                    if (is_file($path . $phpFile)) {
                        //echo "Found file $phpFile in $path<br>";
                        $controllerName = $head;
                        $commandArray = array_splice($commandArray, 2);
                    } else {
                        $controllerName = 'root';
                        $commandArray = array_splice($commandArray, 1);
                    }
                }
            } else {
                $controllerName = "root";
                $commandArray = array_splice($commandArray, 0);
            }
        }

        if (count($commandArray) > 0) {
            $controllerFunct = $commandArray[0];
            $commandArray = array_splice($commandArray, 1);
        }
        $this->setData($controllerDir, $controllerName, $controllerFunct, $commandArray);
    }

    private function setData($controllerDir, $controllerName, $functionName, $parameters) {
        $this->parameters = $parameters;
        $this->name = $controllerName;
        $this->function = $functionName;
        $this->directory = $controllerDir;

        $this->queryParams = $_GET;
        $this->cleanInput($this->queryParams);

        $this->postParams = $_POST;
        $this->cleanInput($this->postParams);

        $this->reqParams = $_REQUEST;
        $this->cleanInput($this->reqParams);
    }

    private function ctrlName($name) {
        $parts = explode('_', $name);
        $n = '';
        foreach($parts as $p) {
            $n .= ucfirst($p);
        }
        $name = $n;
        return $name . "Controller";
    }

    private function ctrlFileName($name) {
        return $this->ctrlName($name) . ".php";
    }

    public function getControllerDirectory() {
        return $this->directory;
    }

    public function getControllerName() {
        return $this->name;
    }

    public function getControllerClassName() {
        return $this->ctrlName($this->name);
    }

    public function getControllerFileName() {
        return $this->ctrlFileName($this->name);
    }

    public function getControllerFilePath() {
        $path = \asf\utils\util::end_slash(CONTROLLERS_PATH);
        $dir = $this->getControllerDirectory();
        if (!empty($dir)) {
            $path .= \asf\utils\util::end_slash($dir);
        }

        $fileName = $this->getControllerFileName();
        $path .= $fileName;
        return $path;
    }

    public function getFunction() {
        return $this->function;
    }

    public function getQueryParams() {
        return $this->queryParams;
    }

    public function getPostParams() {
        return $this->postParams;
    }

    public function getReqParams() {
        return $this->reqParams;
    }

    public function getParameters() {
        return $this->parameters;
    }

    public function hasSingleIntParam() {
        $params = $this->getParameters();
        if (count($params) == 1 && ctype_digit($params[0])) {
            return true;
        }
        return false;
    }

    public function getControllerBaseUrl() {
        $url = BASE_URL;
        if (!empty($this->directory)) {
            $url .= $this->directory . "/";
        }

        if ((!empty($this->name)) && $this->name != 'root') {
            $url .= $this->name . "/";
        }
        return $url;
    }

    /**
     * Get the full-qualified namespaced controller class
     */
    public function getControllerClass() {
        $ns = APP_NAMESPACE;
        if (!\asf\utils\util::ends_with("\\", $ns)) {
            $ns = $ns . '\\';
        }
        if (!\asf\utils\util::starts_with($ns, '\\')) {
            $ns = '\\' . $ns;
        }
        //$ns = ltrim($ns, "\\");
        $ns .= "controllers\\";
        $dir = $this->getControllerDirectory();
        if (!empty($dir)) {
            $ns .= $dir . '\\';
        }
        $ns = str_replace("/", '\\', $ns);
        $ns .= $this->getControllerClassName();
        return $ns;
    }

    public function toUrl($includeParameters = false) {
        $url = $this->getControllerBaseUrl();
        if (!empty($this->function)) {
            $url .= $this->function . "/";
        }

        if ($includeParameters == true) {
            foreach ($this->parameters as $p) {
                $url .= $p . "/";
            }
        }
        return $url;
    }

    public function currentUrl() {
        $url = $this->toUrl(true);
        if (!empty($this->queryParams)) {
            return $url . "?" . http_build_query($this->queryParams);
        }
        return $url;
    }
    
    public function isXhr() {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH'])
                && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest') {
            return true;
        }
        return false;
    }

    private function cleanInput(&$arr) {
        foreach ($arr as $k => $v) {
            if (is_array($v)) {
                $this->cleanInput($v);
            } else {
                //$arr[$k] = (trim(urldecode($v)));
                $arr[$k] = (trim(($v)));
            }
        }
    }
}
