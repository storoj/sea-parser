<?php

function array_merge_numeric($array1, $array2) {
    foreach ($array2 as $key => $value) {
        $array1[$key] = $value;
    }
    return $array1;
}

function arrayMaxValueOfKey($array, $key)
{
    $max = NULL;
    foreach ($array as $item) {
        if (isset($item[$key]) && (is_null($max) || $item[$key] > $max)) {
            $max = $item[$key];
        }
    }
    return $max;
}

function queryStringFromArray($params){
    if(!is_array($params)){
        return false;
    }

    $queryString = array();
    foreach($params as $key => $value){
        if (!is_null($value)) {
            $value = '='.urlencode($value);
        }
        $queryString[] = $key.$value;
    }
    return empty($queryString)
        ? ''
        : '?'.implode('&', $queryString);
}

function redirect($url){
    header('Location: '.$url);
    ob_end_flush();
    die();
}

function checkEmail($email) {
    return preg_match('/^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/', trim($email));
}

function validateNotEmpty($value){
    if(empty($value)){
        return 'Поле не заполнено(empty)';
    }
    return true;
}

function is_positive($num) {
    if (is_numeric($num) && $num > 0) {
        return true;
    }

    return false;
}

function array_slice_keys($haystack, $keys){
    $result = array();
    foreach($keys as $key){
        if(isset($haystack[$key])){
            $result[$key] = $haystack[$key];
        }
    }
    return $result;
}

function array_values_for_keys($array, $keys) {
    $result = array();

    foreach ($array as $row) {
        $result[] = array_slice_keys($row, $keys);
    }

    return $result;
}

function getResizedFileName($path_mod, $modifier) {
    $pathinfo = pathinfo($path_mod);
    if (!empty($modifier)) $modifier = '_'.$modifier;
    if (isset($pathinfo['dirname']) && isset($pathinfo['filename']) && isset($pathinfo['extension'])) {
        return $pathinfo['dirname'].'/'
            . preg_replace("/^(.*?)(_[^_]*)?$/", "$1".$modifier, $pathinfo['filename'])
            . '.'. $pathinfo['extension'];
    } else {
        return NULL;
    }
}

function getMultipleFormText($n, $form_0, $form_1, $form_2){
    if ($n >= 11 && $n <= 19)
        return $form_0;
    elseif ($n % 10 == 1)
        return $form_1;
    elseif (($n % 10 == 2) || ($n % 10 == 3) || ($n % 10 == 4))
        return $form_2;
    else
        return $form_0;
}

/* Saved for compatibility */
function getMultipleForm($n, $form_0, $form_1, $form_2, $separator = ' ') {
    return $n.$separator.getMultipleFormText($n, $form_0, $form_1, $form_2);
}

function getMimeType($file_path) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime_type = @finfo_file($finfo, $file_path);

    return $mime_type;
}

function isImage($file_path){
    $mime = explode('/', getMimeType($file_path));
    return $mime[0] == 'image';
}

function cutString($string, $length){
    $string = strip_tags($string);

    if(mb_strlen($string, 'UTF-8') > $length){
        $part = mb_substr($string, 0, $length, 'UTF-8').'...';
    } else {
        $part = $string;
    }

    return $part;
}

function xml2array ($xmlObject, $out = array () ) {
    foreach ( (array)$xmlObject as $index => $node )
        $out[$index] = ( is_object ( $node ) ) ? xml2array ( $node ) : $node;

    return $out;
}

function wrapLinks($str) {
    return preg_replace('#(http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?)#i',
        '<a href="$1">$1</a>', $str);
}

function delTextStart($text) {
    return preg_replace('#^\"Мир\s+квартир\"\s*-\s*(?:квартиры|недвижимость)\s*в\s*центре\s*города[.!\s]*#iu', '', $text);
}

function formatDate($date) {
    $months = array('января', 'февраля', 'марта', 'апреля', 'мая', 'июня',
        'июля', 'августа', 'сентября', 'октября', 'ноября', 'декабря');
    return date('j', $date) . ' ' . $months[date('m', $date) - 1] . ' ' . date('Y', $date);
}
function count_visited($cookie_name = 'visited') {
    $array = array();
    if(isset($_COOKIE[$cookie_name])) {
        $array = json_decode($_COOKIE[$cookie_name], true); //возвращаем массиву рабочее состояние
    }
    return count($array);
}

function getDefaultFilePath($default, $path = NULL){
    if(is_null($path) || empty($path) || !file_exists(PATH_ROOT.$path)){
        return $default;
    }

    return $path;
}

function checkAvatar($path, $size = 72) {
    $default = '/img/no_photo_'.$size.'.jpg';
    $to_find = 'tmb'.$size;
    $src = getResizedFileName($path, $to_find);

    return getDefaultFilePath($default, $src);
}
?>