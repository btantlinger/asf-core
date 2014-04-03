<?php namespace asf\data;
use \asf\db\Database;

/*
 *  sqlUtils.php
 *  Created on Feb 27, 2013 7:30:04 PM by bob
 */

/**
 * Description of sqlUtils
 *
 * @author bob
 */
class sqlUtil {
    
    private $db;
    
    public function __construct(Database &$db) {
        $this->db = $db;
    }

    public function getOrderByClauseFromParameters($params, $default = '') {
        $sql = '';
        if (!empty($params['order_by'])) {
            $sql .= " ORDER BY " . $this->db->escape($params['order_by']) . " ";
            if (!empty($params['order'])) {
                $sql .= $this->db->escape(strtoupper($params['order'])) . " ";
            }
        }
        if (empty($sql)) {
            if (!empty($default)) {
                return " $default ";
            }
        }
        return $sql;
    }

    public function getLimitClauseFromParameters($params) {
        if (isset($params['limit1']) && isset($params['limit2'])) {
            return " LIMIT " . $this->db->escape($params['limit1']) . ", " . $this->db->escape($params['limit2']) . " ";
        }
        return " ";
    }

    /**
     * adds an SQL condition to the conditionsArray if and only if it appears in the parametersArray
     *
     * E.g  name = 'Bob Smith'
     *
     * @param array $conditionsArray
     * @param array $parametersArray
     * @param array $key  The array key
     */
    public function addCondition(&$conditionsArray, &$parametersArray, $key, $tableName = '', $operator = '=') {
        if (isset($parametersArray[$key]) && (!\asf\utils\util::is_str_empty($parametersArray[$key]))) {
            if (!empty($tableName)) {
                $column = $tableName . "." . $key;
            } else {
                $column = $key;
            }
            $conditionsArray[] = " $column $operator '" . $this->db->escape($parametersArray[$key]) . "' ";
        }
    }

    public function addNullableCondition(&$conditionsArray, &$parametersArray, $key, $tableName = '') {
        if (!\asf\utils\util::is_str_empty($parametersArray[$key])) {
            if (!empty($tableName)) {
                $column = $tableName . "." . $key;
            } else {
                $column = $key;
            }

            $value = $this->db->escape($parametersArray[$key]);
            if ($value == 'NULL') {
                $conditionsArray[] = " $column IS NULL ";
            } else if ($value == 'NOT NULL') {
                $conditionsArray[] = " $column IS NOT NULL ";
            } else {
                $conditionsArray[] = " $column = '" . $value . "' ";
            }
        }
    }

    /**
     * Adds an SQL condition to the conditionsArray for between numeric values.  The parameter MUST be
     * formatted such as 100 - 200 (note space before and after '-'), and MUST contains numeric values.
     *
     * @param array $conditionsArray
     * @param array $parametersArray
     * @param array $key  The array key
     * @param string $tableName  the name of the table
     */
    public function addNumericBetweenCondition(&$conditionsArray, &$parametersArray, $key, $tableName = '') {
        if (!\asf\utils\util::is_str_empty($parametersArray[$key])) {
            if (!empty($tableName)) {
                $column = $tableName . "." . $key;
            } else {
                $column = $key;
            }

            $val = $this->db->escape($parametersArray[$key]);
            $parts = explode(" - ", $val);
            if (count($parts) == 2) {
                $a1 = trim($parts[0]);
                $a2 = trim($parts[1]);
                if (is_numeric($a1) && is_numeric($a2)) {
                    $conditionsArray[] = " ($column BETWEEN '$a1' AND '$a2') ";
                }
            }
        }
    }

    public function addLikeTextCondition(&$conditionsArray, &$parametersArray, $key, $tableName = '') {
        if (!empty($parametersArray[$key])) {
            if (!empty($tableName)) {
                $column = $tableName . "." . $key;
            } else {
                $column = $key;
            }

            $text = $this->db->escape($parametersArray[$key]);
            $conditionsArray[] = " $column LIKE '%" . $text . "%' ";
        }
    }

    public function addIdInCondition(&$conditionsArray, &$parametersArray, $key = 'ids', $tableName = '', $idField = 'id') {
        if ((!empty($parametersArray[$key])) && is_array($parametersArray[$key])) {
            $idsArr = $parametersArray[$key];
            $elems = array();
            foreach ($idsArr as $id) {
                if (ctype_digit($id)) {
                    $elems[] = $id;
                }
            }

            if (count($elems) > 0) {
                $inStr = implode(",", $elems);
                $field = $idField;
                if (!empty($tableName)) {
                    $field = "$tableName.$field";
                }
                $conditionsArray[] = " $field IN (" . $inStr . ") ";
            }
        }
    }

    public function addContainsTextCondition(&$conditionsArray, $textFragment, $dbTextColsArray) {
        if (!empty($textFragment)) {
            $orConds = array();
            $textFragment = $this->db->escape($textFragment);
            foreach ($dbTextColsArray as $col) {
                $orConds[] = " $col LIKE '%" . $textFragment . "%' ";
            }

            if (!empty($orConds)) {
                $ors = implode(" OR ", $orConds);
                $conditionsArray[] = "($ors)";
            }
        }
    }

    public function addStartsWithCondition(&$conditionsArray, $textFragment, $dbTextColsArray) {
        if (!empty($textFragment)) {
            $orConds = array();
            $textFragment = $this->db->escape($textFragment);
            foreach ($dbTextColsArray as $col) {
                $orConds[] = " $col LIKE '" . $textFragment . "%' ";
            }

            if (!empty($orConds)) {
                $ors = implode(" OR ", $orConds);
                $conditionsArray[] = "($ors)";
            }
        }
    }

    //TODO document this method
    /**
     *
     *
     *
     * @param array $paramsArr  the query params
     * @param string $type the type, eg. quotes, calls, contacts
     * @param string $dateField the date field in the table
     */
    public function addDateRangeCondition(&$conditionsArray, &$paramsArr, $dbDateField, $key = "dated_by") {
        $type = $key;
        $dateField = $dbDateField;

        if (!empty($paramsArr[$type])) {
            $startDate = false;
            $endDate = false;
            if ($paramsArr[$type] == 'range') {
                $startDate = date('Y-m-d H:i:s', strtotime($paramsArr['from_date']));
                $endDate = date('Y-m-d H:i:s', strtotime($paramsArr['to_date']));
            } else if (ctype_digit($paramsArr[$type])) {
                //is it a year
                $startDate = date('Y-m-d H:i:s', strtotime("1/1/" . $paramsArr[$type] . " 00:00:00"));
                $yr = "12/31/" . $paramsArr[$type] . " 23:59:59";
                $endDate = date('Y-m-d H:i:s', strtotime($yr));
                //echo "$startDate to $endDate";
                unset($paramsArr['from_date']);
                unset($paramsArr['to_date']);
            } else {
                //must be a month
                $startDate = date('Y-m-d H:i:s', strtotime($paramsArr[$type]));
                $lastDay = date('t', strtotime($startDate)); //last day of specified month
                $year = date('Y', strtotime($startDate)); // year
                $month = date('m', strtotime($startDate));
                $startDate = date('Y-m-d H:i:s', strtotime("$year-$month-1 00:00:00"));
                $endDate = date('Y-m-d H:i:s', strtotime("$year-$month-$lastDay 23:59:59"));
                unset($paramsArr['from_date']);
                unset($paramsArr['to_date']);
            }

            if ($startDate !== false && $endDate !== false) {
                $conditionsArray[] = "( $dateField > '$startDate' AND $dateField < '$endDate' )";
            }
        } else if (!empty($paramsArr['period'])) {
            $ts = strtotime($paramsArr['period']);
            if ($ts !== false) {
                $mysqldate = mysqlDateTime($ts); //date('Y-m-d H:i:s', $ts);
                $conditionsArray[] = " $dateField > '$mysqldate' ";
            }
        }
    }    
}
//EOF