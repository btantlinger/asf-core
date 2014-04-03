<?php
namespace asf\asset;

use MatthiasMullie\Minify;
use asf\utils\util;
/*
 *  AssetManager.php
 *  Created on Dec 7, 2012 7:59:38 PM by bob
 */

/**
 * Description of AssetManager
 *
 * @author bob
 */
class AssetManager {
    const SPLIT = "<!-- split file here -->";

    protected $js = array();
    protected $css = array();
    protected $baseUrl;
    protected $serverRoot;
    protected $minifyOutputPath;
    protected $moduleRoot = null;

    public function __construct($baseUrl = null, $serverRoot = null, $moduleRoot = null, $minifyOutputPath = false) {
        $this->baseUrl = $baseUrl === null ? BASE_URL : util::end_slash($baseUrl);
        $this->serverRoot = $serverRoot === null ? SRV_ROOT : util::end_slash($serverRoot);
        $this->moduleRoot = $moduleRoot === null ? $this->serverRoot . "modules/" : util::end_slash($moduleRoot);
        $this->minifyOutputPath = $minifyOutputPath;
        \asf\core\View::getInstance()->getView()->addPath("template", $this->moduleRoot);
    }

    public function getBaseUrl() {
        return $this->baseUrl;
    }

    public function getServerRoot() {
        return $this->serverRoot;
    }

    public function getMinifyOutputPath() {
        return $this->minifyOutputPath;
    }

    public function load_module($mod) {
        $view = \asf\core\View::getInstance()->getView();
        $globPath = '';
        $output = '';
        if (is_dir($this->moduleRoot . $mod)) {
            if (is_file($this->moduleRoot . "$mod/view.tpl.php")) {
                $output = $view->getOutput("$mod/view.tpl.php");
            } else if (is_file($this->moduleRoot . "$mod/view.html")) {
                $output = file_get_contents($this->moduleRoot . "$mod/view.html");
            }
            $globPath = $this->moduleRoot . $mod;
        }

        if (!empty($globPath)) {
            $files = $this->globr($globPath, "css");
            foreach ($files as $f) {
                $this->add_css($f);
            }

            $files = $this->globr($globPath, "js");
            foreach ($files as $f) {
                $this->add_js($f);
            }
        }
        if (!empty($output)) {
            $view->$mod = $output;
        }
        return $output;
    }

    private function globr($sDir, $ext) {
        $files = array();
        $it = new \RecursiveDirectoryIterator($sDir);
        $display = array($ext);
        foreach (new \RecursiveIteratorIterator($it) as $file) {
            $v = explode('.', $file);
            $v = array_pop($v);
            $v = strtolower($v);
            if (in_array($v, $display)) {
                $files[] = $file->getRealPath();
            }
        }
        return $files;
    }

    public function add_js($file) {
        $this->add_file($file, $this->js);
    }    

    public function add_css($file) {
        $this->add_file($file, $this->css);
    }
    
    public function split_js() {
        $this->add_file(self::SPLIT, $this->js);
    }
    
    public function split_css() {
        $this->add_file(self::SPLIT, $this->css);
    }

    private function add_file($file, array &$arr) {        
        if($file == self::SPLIT) {
            $last = end($arr);
            if($last != self::SPLIT) {
                $arr[] = self::SPLIT;
            }                        
        } else if (!in_array($file, $arr)) {
            if (!$this->is_url($file)) {
                if (!util::starts_with($file, $this->serverRoot)) {
                    throw new \Exception($file . " must be a decendant of the server root: " . $this->serverRoot);
                }
            }
            $arr[] = $file;
        }
    }

    public function to_js_tag(&$f) {
        return '<script type="text/javascript" src="' . $f . '"></script>' . PHP_EOL;
    }

    public function to_css_tag(&$f) {
        return '<link rel="stylesheet" type="text/css" href="' . $f . '" />' . PHP_EOL;
    }

    public function get_css_files() {
        return $this->css;
    }

    public function get_css_urls() {
        return $this->get_file_list($this->css, "css");
    }

    public function get_js_files() {
        return $this->js;
    }

    public function get_js_urls() {
        return $this->get_file_list($this->js, "js");
    }

    private function get_file_list(array &$arr, $ext) {
        if ((!empty($this->minifyOutputPath)) && ($ext == "js" || $ext == "css")) {
            $out = array();
            $assets = $this->chunk_by_url($arr);

            foreach ($assets as $a) {                
                $min = $ext == "js" ? new \MatthiasMullie\Minify\JS() : new \MatthiasMullie\Minify\CSS();
                
                if (is_array($a)) {
                    //compute a crc32 hash of these that we will use for the file name
                    //try to do at least 2 path elements AND all together to reduce
                    //the chance of a hash collision
                    //see: http://stackoverflow.com/questions/1515914/crc32-collision
                    $fname = "";
                    $n = 0;
                    $filesToMinify = array();
                    foreach ($a as $file) {
                        if ($n <= 1) {
                            $fname .= crc32($file);
                        }

                        $filesToMinify[] = $file;
                        $n++;
                    }
                    $fname .= crc32(json_encode($a));
                    $outfile = util::end_slash($this->minifyOutputPath) . $ext . $fname . ".$ext";
                    if (!is_file($outfile)) {
                        //make sure files exist
                        foreach ($filesToMinify as $ftm) {
                            if (!is_file($ftm)) {
                                throw new \Exception("File $ftm does not exist");
                            }
                            $min->add($ftm);
                        }
                        $min->minify($outfile);
                    }
                    $out[] = $this->path_to_url($outfile);
                } else {
                    $out[] = $a;
                }
            }
            return $out;
        }

        $uu = array();
        foreach ($arr as $a) {
            if($a !== self::SPLIT) {
                $uu[] = $this->path_to_url($a);
            }
        }
        return $uu;
    }

    private function path_to_url($path) {
        $p = strpos($path, $this->serverRoot);
        if ($p === 0) {
            $path = util::str_replace_count($this->serverRoot, "", $path, 1);
            $path = ltrim($path, DIRECTORY_SEPARATOR);
            $path = util::end_slash($this->baseUrl) . $path;
        }
        return $path;
    }

    private function chunk_by_url(&$arr) {
        $out = array();
        $i = 0;
        foreach ($arr as $f) {
            if ($this->is_url($f)) {
                $out[] = $f;
                $i+=2;
            } else if($f == self::SPLIT) {
                $i+=2;
            } else {
                $out[$i][] = $f;
            }
        }
        return $out;
    }

    public function get_js_html() {
        $urls = $this->get_js_urls();
        $html = "";
        foreach ($urls as $u) {
            $html .= $this->to_js_tag($u);
        }
        return $html;
    }

    public function get_css_html() {
        $urls = $this->get_css_urls();
        $html = "";
        foreach ($urls as $u) {
            $html .= $this->to_css_tag($u);
        }
        return $html;
    }

    public function print_css_html() {
        echo $this->get_css_html();
    }

    public function print_js_html() {
        echo $this->get_js_html();
    }

    public function print_all() {
        $this->print_css_html();
        $this->print_js_html();
    }

    private function is_url($f) {
        if (filter_var($f, FILTER_VALIDATE_URL) === FALSE) {
            return false;
        }
        return true;
    }
}
//EOF