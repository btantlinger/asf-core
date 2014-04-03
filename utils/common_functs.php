<?php
/*
 * These functions are here for historical reasons. They should all be considered deprecated.
 * This file should only be included if needed by an older app.
 */
function baseURL() {
    return BASE_URL;
}

function basePath() {
    return BASE_PATH;
}

function getConfigArray() {
    return \asf\core\Config::getInstance()->getConfigArray();
}

function startsWith($haystack, $needle, $case = true) {
   return \asf\utils\util::starts_with($haystack, $needle, $case);
}

function endsWith($haystack, $needle, $case = true) {
    return \asf\utils\util::ends_with($haystack, $needle, $case);
}

function lastIndexOf($haystack, $needle, $offset = 0) {
    return \asf\utils\util::last_index_of($haystack, $needle, $offset);
}

function arrayToObject($array) {
    return \asf\utils\util::array_to_object($array);
}

function str_replace_count($search, $replace, $subject, $times) {
    return \asf\utils\util::str_replace_count($search, $replace, $subject, $times);
}

function isStrEmpty($val) {
    return \asf\utils\util::is_str_empty($val);
}

function is_assoc($var) {
    return \asf\utils\util::is_assoc($var); 
}

function str_lreplace($search, $replace, $subject) {
    return \asf\utils\util::str_lreplace($search, $replace, $subject);
    
}

function ensureEndSlash($url) {
    return \asf\utils\util::end_slash($url);
}

function isArrayOfIntegerValues($array) {
    return \asf\utils\util::is_array_of_ints($array);
}

function unset_by_value(&$array, $val = '') {
    return \asf\utils\util::unset_by_value($array, $val);
}

function dirify($s) {
    return \asf\utils\util::dirify($s);
}

function in_arrayi($needle, $haystack) {
    return \asf\utils\util::in_arrayi($needle, $haystack);
}

function getVistorIP() {
    return \asf\utils\util::get_client_ip();
}

function gen_uuid($sep='-') {
    return \asf\utils\util::gen_uuid($sep);
}

function sanitize_recursive($s) {
    return \asf\utils\util::sanitize_recursive($s);
}

function killSession() {
        //make sure our session is good and dead
        session_destroy();
        session_start();
        $_SESSION = array();
        \asf\core\SessionManager::regenerateSession();
 }

function setcookielive($name, $value='', $expire=0, $path='', $domain='', $secure=false, $httponly=false) {
    //set a cookie as usual, but ALSO add it to $_COOKIE so the current page load has access
    $_COOKIE[$name] = $value;
    return setcookie($name,$value,$expire,$path,$domain,$secure,$httponly);
}

function currentWeekStartDate($first=1, $format='Y-m-d') {
    return weekStartDate(date('W'), date('Y'), $first, $format);
}

function weekStartDate($wk_num, $yr, $first = 1, $format = 'Y-m-d') {
    $wk_ts = strtotime('+' . $wk_num . ' weeks', strtotime($yr . '0101'));
    $mon_ts = strtotime('-' . date('w', $wk_ts) + $first . ' days', $wk_ts);
    return date($format, $mon_ts);
}

function mysqlDateTime($timestamp=NULL) {
//    if (is_null($timestamp)) {
//        return date('Y-m-d H:i:s');
//    }
//    return date('Y-m-d H:i:s', $timestamp);
    return dateTimeInGMT($timestamp);
}

function toDate($mysqlDateTime) {
    return gmtDatetimeToLocal($mysqlDateTime, "M j Y");
}

function toISO_8601Date($mysqlDateTime) {
    return gmtDatetimeToLocal($mysqlDateTime, "Y-m-d");
}

function dateTimeInGMT($ts=NULL) {
    if (is_null($ts)) {
        $ts = time();
    }
    return date("Y-m-d H:i:s", $ts - date("Z", $ts));
}

function gmtDatetimeToLocal($datetime, $format='Y-m-d H:i:s') {
    $time = strtotime($datetime);
    return date($format, $time + date("Z", $time));
}

function debugDump($var) {
    echo "<pre>\n";
    echo "DEBUG DUMP\n=====================================================\n";
    print_r($var);
    echo "\n</pre>\n";
    die();
}

function extractLikeArrays(&$data, $arrayOfKeys, $unsetKeys=true, $replacementKeys=NULL) {
    $arrayOfArrays = array();
    $repKeyCount = -1;
    if ($replacementKeys != NULL) {
        $repKeyCount = count($replacementKeys);
    }

    $keyCount = count($arrayOfKeys);
    for ($i = 0; $i < $keyCount; $i++) {
        if ($i < $repKeyCount) {
            $k = $repKeyCount[$i];
        } else {
            $k = $arrayOfKeys[$i];
        }

        $arrayOfArrays[$k] = $data[$arrayOfKeys[$i]];
        if ($unsetKeys) {
            unset($data[$arrayOfKeys[$i]]);
        }
    }

    return convergeLikeArrays($arrayOfArrays);
}

function convergeLikeArrays($arrayOfArrays) {
    $lastCount = -1;
    $out = array();
    foreach ($arrayOfArrays as $arr) {
        $c = count($arr);
        if ($lastCount != -1 && $c != $lastCount) {
            throw new Exception("Array sizes do not match");
        }
        $lastCount = $c;
    }

    for ($i = 0; $i < $lastCount; $i++) {
        $arr = array();
        foreach ($arrayOfArrays as $k => $v) {
            $arr[$k] = $arrayOfArrays[$k][$i];
        }
        $out[] = $arr;
    }

    return $out;
}
//EOF