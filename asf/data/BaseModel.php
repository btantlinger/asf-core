<?php namespace asf\data;
use \Exception;
use \asf\utils\Inflector;
use \asf\db\DBSingleton;
use \asf\db\Database;
/**
 * The super class all models must inherit from.
 *
 * Subclass names must end with Model such as FooModel, FoobarModel, etc
 *
 * Only the first letter of the class name, and Model can be capitalized.
 *
 * Accessor methods should take the form _getXXX($options)
 *
 *
 *
 * @author bob
 */


abstract class BaseModel { 

    private $fields = null;
    private $reqFields = null;
    
    protected $modelTableName = null;
    protected $primaryKey = "id";
    protected $validators = array();
    protected $displayName = null;
    private $logger;


    protected $db;

    public function __construct(Database &$db=null) {
        $this->logger = \asf\utils\LogUtil::getLogger(__CLASS__);
        $claz = explode('\\', get_class($this));
        $clName = end($claz);        
        if ($this->modelTableName === null) {
            $m = "Model";
            $mLen = strlen($m);
            if(\asf\utils\util::ends_with($clName, $m) && strlen($clName) > $mLen) {
                $modelName = substr($clName, 0, ($mLen * -1));
                if($this->displayName === null) {
                    $this->displayName = $modelName;
                }                
                $modelName = strtolower($modelName);
                $inflector = new Inflector();
                $this->modelTableName = $inflector->pluralize($modelName);
            }            
        }
        $this->db = ($db === null) ? DBSingleton::getInstance()->getDatabase() : $db;
        if($this->displayName === null) {
            $this->displayName = $clName;
        }
        $this->logger->trace("Initialized model {$this->displayName} for table {$this->modelTableName}");
        $this->onInit();
    }
    
    /**
     * Called when the model object is initialized. Subclasses can override this
     */
    protected function onInit() {
        
    }
    
    public function setDatabase(Database &$db) {
        $this->db = $db;
    }

    public function getDatabase() {
        return $this->db;
    }

    public function getPrimaryKey() {
        return $this->primaryKey;
    }

    public function getModelTableName() {
        return $this->modelTableName;
    }
    
    public function getDisplayName() {
        return $this->displayName;
    }

    public function getFields() {
        if ($this->fields === null) {
            $this->fields = $this->db->getColumnNames($this->getModelTableName());
        }
        return $this->fields;
    }

    public function getRequiredFields() {
        if ($this->reqFields === null) {
            $rules = $this->getValidationRules();
            $this->reqFields = array();
            foreach ($rules as $key => $val) {
                $c = explode("|", $val['rules']);
                if (in_array("required", $c)) {
                    $this->reqFields[] = $key;
                }
            }
        }
        return $this->reqFields;
    }

    public function add(array $modelData, $shouldValidate=true) {
        $id = false;
        $this->db->beginTransaction();
        try {
            $modelData = $this->onBeforeAdd($modelData);
            $modelData = ModelEventManager::fireBeforeAdd(get_class($this), $modelData);
            $data = $this->allowed($this->getFields(), $modelData);
            if($shouldValidate) {
                $this->assertValid($data);
            }
            unset($data[$this->primaryKey]);
            $id = $this->db->query_insert($this->getModelTableName(), $data);
            $this->onAfterAdd($id);
            ModelEventManager::fireAfterAdd(get_class($this), $id);
            $this->db->commit();
            $this->logger->debug("Added {$this->displayName} item {$id} to {$this->modelTableName}");
            $this->logger->debug($modelData);
        } catch (\Exception $ex) {
            $this->logger->warn("Rolling back add of {$this->displayName} item to {$this->modelTableName}", $ex);
            $this->logger->warn($modelData);
            $this->db->rollBack();
            throw $ex;
        }
        return $id;
    }
    
    public function addMany(array $arrays, $shouldValidate=true) {
        $ids = array();
        $this->logger->debug("Adding many {$this->displayName} items to {$this->modelTableName}");
        $this->db->beginTransaction();
        try {
            foreach($arrays as $modelData) {
                $ids[] = $this->add($modelData, $shouldValidate);
            }
            $this->logger->debug("Added many {$this->displayName} items to {$this->modelTableName}");
            $this->db->commit();
        } catch(\Exception $ex) {
            $this->logger->warn("Rolling back add many of {$this->displayName} items to {$this->modelTableName}", $ex);
            $this->db->rollBack();
            throw $ex;
        }
        return $ids;
    }

    public function update(array $modelData, $shouldValidate=true) {
        $this->db->beginTransaction();
        try {
            $modelData = $this->onBeforeUpdate($modelData);
            $modelData = ModelEventManager::fireBeforeUpdate(get_class($this), $modelData);
            $data = $this->allowed($this->getFields(), $modelData);
            $this->assertRequired(array($this->primaryKey), $data);            
            if($shouldValidate) {
                $this->assertValidExistingElements($data);
            }
            $eid = $this->db->quote($data[$this->primaryKey]); 
            $id = $data[$this->primaryKey];
            unset($data[$this->primaryKey]);
            $result = $this->db->query_update($this->getModelTableName(), $data, "{$this->primaryKey} = $eid");
            if($result === false) {
                throw new \Exception("Update failed on table " . $this->getModelTableName());
            } 
//            else if($result == 0) {
//                throw new \Exception("No such record in table " . $this->getModelTableName());
//            }
            $this->onAfterUpdate($id);
            ModelEventManager::fireAfterUpdate(get_class($this), $id);
            $this->db->commit();
        } catch (\Exception $ex) {
            $this->db->rollBack();
            throw $ex;
        }
    }
    
    public function updateMany(array $arrays, $shouldValidate=true) {
        $this->db->beginTransaction();
        try {
            foreach($arrays as $modelData) {
                $this->update($modelData, $shouldValidate);
            }
            $this->db->commit();
        } catch(\Exception $ex) {
            $this->db->rollBack();
            throw $ex;
        }      
    }
    
    
    public function delete(array $modelData) {
        $num = 0;
        $this->db->beginTransaction();
        try {            
            $modelData = $this->onBeforeDelete($modelData);
            $modelData = ModelEventManager::fireBeforeDelete(get_class($this), $modelData);
            $this->assertRequired(array($this->primaryKey), $modelData);
            $eid = $this->db->quote($modelData[$this->primaryKey]);
            $tbl = $this->getModelTableName();
            $num = $this->db->exec("DELETE FROM $tbl WHERE {$this->primaryKey} = $eid");
            $this->onAfterDelete($modelData[$this->primaryKey]);
            ModelEventManager::fireAfterDelete(get_class($this), $modelData[$this->primaryKey]);
            $this->db->commit();            
        } catch(\Exception $ex) {
            $this->db->rollBack();
            throw $ex;
        }
        return $num;
    }
    
    public function deleteMany(array $arrays) {
        $this->db->beginTransaction();
        try {
            foreach($arrays as $modelData) {
                $this->delete($modelData);
            }
            $this->db->commit();
        } catch(\Exception $ex) {
            $this->db->rollBack();
            throw $ex;
        }
    } 

    
    /**
     * This method is called before a single item is added. Subclasses can override this
     * @param $data
     * @return  The data that was passed to this method.
     * 
     */
    protected function onBeforeAdd(array $data) {
        return $data;
    }
    
    /**
     * This method is called before a single item is updated. Subclasses can override this
     * @param $data
     * @return  The data that was passed to this method.
     * 
     */
    protected function onBeforeUpdate(array $data) {
        return $data;
    }
    
    /**
     * This method is called before a single item is deleted. Subclasses can override this
     * @param $data
     * @return  The data that was passed to this method.
     * 
     */
    protected function onBeforeDelete(array $data) {
        return $data;
    }
    
    
    
    /**
     * This method is called after a single item is added. Subclasses can override this
     * @param id  the pk of the item that was added
     * 
     */
    protected function onAfterAdd($id) {

    }
    
    /**
     * This method is called after a single item is updated. Subclasses can override this
     * @param id  the pk of the item that was updated
     * 
     */
    protected function onAfterUpdate($id) {
        
    }
    
    /**
     * This method is called after a single item is deleted. Subclasses can override this
     * @param $id the pk of the item that was updated
     * 
     */
    protected function onAfterDelete($id) {

    }
    
    protected function assertValidExistingElements(array $data, array $validators=null) {
        if($validators === null) {
            $validators = $this->getValidationRules();
        }
        $rules = array();
        foreach ($data as $key => $val) {
            if (isset($validators[$key])) {
                $rules[$key] = $validators[$key];
            }
        }
        $this->assertValid($data, $rules);
    }

    protected function assertValid(array $data, array $validators=null) {
        $result = $this->validate($data, $validators);
        if ($result !== true) {
            throw new ValidationException("Invalid " . $this->getDisplayName(), $result);
        }
    }

    protected function validate(array $data, array $validators=null) {
        if($validators === null) {
            $validators = $this->getValidationRules();
        }
        $rules = array();
        foreach ($validators as $k => $v) {
            $rules[$k] = $v['rules'];
        }

        if (!empty($rules)) {
            $gvalidator = new \asf\utils\GUMP();
            $result = $gvalidator->validate($data, $rules);
            if ($result !== true) {
                $errors = array();
                foreach ($result as $err) {
                    $field = $err['field'];
                    $errors[$field] = isset($validators[$field]['message']) ? ($validators[$field]['message']) : $err['message'];
                }
                return $errors;
            }
        }
        return true;
    }

    protected function getValidationRules() {
        return $this->validators;
    }
   
    protected function sqlToList($sql, array $sqlParams=array(), RowFilter $rowFilter=null) {
        $sqlResultSet = new SQLResultSet($this);
        $sqlResultSet->setRowFilter($rowFilter);
        $sqlResultSet->setSQL($sql, $sqlParams);        
        return $sqlResultSet;
    }

    protected function sqlToPagedList($sql, array $sqlParams=array(), RowFilter $rowFilter=null, PagerConfig $pagerConfig=null, $countSql=null, array $countSqlParams=null) {
        $sqlResultSet = new PagedSQLResultSet($this, $pagerConfig);        
        $sqlResultSet->setRowFilter($rowFilter);
        $sqlResultSet->setSQL($sql, $sqlParams);
        $sqlResultSet->setCountSQL($countSql, "total", $countSqlParams);
        return $sqlResultSet;
    }
    
    protected function queryBaisc(array $options=array(), $countOnly=false) {
        $tableName = $this->getModelTableName();
        if ($countOnly) {
            $sql = "SELECT COUNT(*) AS total FROM $tableName ";
        } else {
            $sql = "SELECT * FROM $tableName ";
        }

        $conds = array();        
        $sqlUtil = new sqlUtil($this->db);
        foreach ($this->getFields() as $fieldName) {
            $sqlUtil->addCondition($conds, $options, $fieldName, $tableName);
        }

        $where = implode(" AND ", $conds);
        if (!empty($where)) {
            $sql .= " WHERE $where ";
        }

        if (!$countOnly) {
            $sql .= $sqlUtil->getOrderByClauseFromParameters($options, "ORDER BY $tableName.{$this->primaryKey} DESC");
        }
        return $sql;
    }  
    
    /**
     * Checks that the required options exists, throws an exception if not
     *
     * @param array $required
     * @param array $data
     */
    protected function assertRequired($required, $data, $msg=null) {        
        $missingFields = array();
        foreach ($required as $field) {
            if ( (!isset($data[$field])) || (empty($data[$field]) && $data[$field] != '0' /* && (!is_array($data[$field])  )*/ ) ) {
                $missingFields[] = $field;
            }
        }
        
        if(!empty($missingFields)) {
            if(!empty($msg)) {
                throw new ValidationException($this->getModelTableName(), array($msg));
            }
            
            $msgs = array();
            foreach($missingFields as $f) {
                $msgs[$f] = "Missing required field:  $f";
            }
            throw new ValidationException("Invalid " . $this->getModelTableName(), $msgs);
        }
    }
    
    
    /**
     * _required method returns false if the $data array does not contain all of the keys assigned by the $required array.
     *
     * @param array $required
     * @param array $data
     * @return bool
     */
    protected function _required($required, $data) {
        foreach ($required as $field) {
            if ( (!isset($data[$field])) || (empty($data[$field]) && $data[$field] != '0' && (!is_array($data[$field]))) ) {
                return false;
            }
        }
        return true;
    }

    protected function query_single($sql, RowFilter $rowFilter=null, $noSuchItemMsg=null) {
        $r = $this->query_first($sql, $noSuchItemMsg);
        if ($rowFilter != null) {
            $rowFilter->filterRow($r);
        }
        return $r;
    }

    protected function query_first($sql, $noSuchItemMsg=null) {
        $row = $this->db->query_first($sql);
        if (!$row) {
            if ($noSuchItemMsg == null) {
                $noSuchItemMsg = "No Such Item";
            }
            throw new NoSuchItemException($noSuchItemMsg);
        }
        return $row;
    }

    /**
     * _default method combines the options array with a set of defaults giving the values in the options array priority.
     *
     * @param array $defaults
     * @param array $options
     * @return array
     */
    protected function _default($defaults, $options) {
        return array_merge($defaults, $options);
    }

    protected function allowed($allowedFields, $inputArray) {
        $data = array();
        foreach ($allowedFields as $k) {
            if (isset($inputArray[$k])) {
                $data[$k] = $inputArray[$k];
            }
        }
        return $data;
    }    
}
//EOF