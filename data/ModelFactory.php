<?php namespace asf\data;
//use asf\core;

/**
 * Description of ModelFactory
 *
 * @author bob
 */
class ModelFactory {
    
    /**
     * Loads a model
     *
     * If your model is located in a sub-folder, include the relative path from
     * your models folder. For example blog/Comment
     *
     * @param string $model
     * @return Model The model that was loaded
     */
    public static function loadModel($model, $autoLoadMethods=array(), $db=null) {        
        $model = str_replace("/", "\\", $model);
        $ns = APP_NAMESPACE;
        if(!\asf\utils\util::ends_with("\\", $ns)) {
            $ns = $ns . '\\';
        }
        if(!\asf\utils\util::starts_with($ns, '\\')) {
            $ns = '\\' . $ns;
        }
        //$ns = ltrim($ns, "\\");
        $ns .= "models\\";
        
        $modelClass = $ns . $model . "Model"; 
       
        $loadedModel = new $modelClass();
        
        if(!$loadedModel instanceof BaseModel) {
            throw new NoSuchModelException("$modelClass is not an instance of BaseModel");
        }     
        
        if($db !== null) {
            $loadedModel->setDatabase($db);
        }

        $data = array();
        if (is_array($autoLoadMethods) && !empty($autoLoadMethods)) {
            $methods = get_class_methods($loadedModel);
            foreach ($methods as $m) {
                if (\asf\utils\util::starts_with($m, "_get")) {
                    $key = \asf\utils\util::str_replace_count("_get", "", $m, 1);
                    if (isset($autoLoadMethods[$key])) {
                        $methodParams = $autoLoadMethods[$key];
                        if (\asf\utils\util::ends_with($key, "List")) {
                            $options = array();
                            $pagerConfig = NULL;
                            
                            $rowFilter = new NullRowFilter();
                            if (is_array($methodParams)) {
                                if (isset($methodParams['options'])) {
                                    $options = $methodParams['options'];
                                }

                                if (isset($methodParams['pagerConfig'])) {
                                    $pagerConfig = $methodParams['pagerConfig'];
                                }

                                if (isset($methodParams['rowFilter'])) {
                                    $rowFilter = $methodParams['rowFilter'];
                                }
                            }

                            if (\asf\utils\util::ends_with($key, "PagedList")) {
                                $data[$key] = $loadedModel->$m($options, $rowFilter, $pagerConfig);
                            } else {
                                $data[$key] = $loadedModel->$m($options, $rowFilter);
                            }
                        } else {
                            $options = array();
                            if (isset($autoLoadMethods[$key]['options'])) {
                                $options = $autoLoadMethods[$key]['options'];
                            } 
                            
                            if (isset($methodParams['rowFilter'])) {
                                $rowFilter = $methodParams['rowFilter'];
                            } else {
                                $rowFilter = new NullRowFilter();
                            }
                            
                            $result = $loadedModel->$m($options, $rowFilter);
                            if ($result) {
                                $data[$key] = $result;
                            }
                        }
                    }
                }
            }
        }

        
        $tmpl =  \asf\core\View::getInstance()->getView();
        if (isset($tmpl->$model)) {
            $tmpl->$model = array_merge($tmpl->$model, $data);            
        } else {
             $tmpl->$model = $data;
        }
        
        $objName = $model . "Data";
        $tmpl->$objName = \asf\utils\util::array_to_object($tmpl->$model);
        return $loadedModel;        
    }    
}
//EOF