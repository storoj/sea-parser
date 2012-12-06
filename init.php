<?php

if (defined('USE_DEBUG') && USE_DEBUG) {
    include('debugger/debugger.php');
    Debugger::instance()->startDebug();
}

// loading class files automatically
function __autoload($class_name) {
    $includeFile = PATH_CLASSES . $class_name.'.php';
    if(!file_exists($includeFile)){
        $class = strtolower(preg_replace('#([^A-Z])([A-Z])#', '$1.$2', $class_name));
        $pathParts = explode('.', $class);

        $dir = $pathParts[0];
        $includeFile = PATH_CLASSES.$dir.'/'.$class_name.'.php';
        if (!(is_dir(PATH_CLASSES.$dir) && file_exists($includeFile))) {
            $class = array_reverse($pathParts);

            $dir = $class[0];
            $fileName = strtolower(implode('.', $class)).'.php';

            if(is_dir(PATH_CLASSES.$dir)){
                $fileName = $dir.'/'.$fileName;
            }

            $includeFile = PATH_CLASSES . $fileName;
        }
    }


    if (file_exists($includeFile)){

        if (USE_DEBUG) {
            Debugger::instance()->addClass($class_name);
        }

        include $includeFile;
    } else {
        #throw new Exception("no file to include", 1);
        #echo 'no file <strong>'.$includeFile.'</strong>';
    }
}

// common functions (global)
include_once(PATH_ROOT . 'helper.php');

// connecting db
if (!DB::getInstance()->connect(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PREFIX)) {
    die ('DB connection error!');
}

// initiate repository list
// comment this when repositories are set and stable
// include('repositories.php');

/*
 * hack for plupload flash runtime
 * setting session id from multipart params to get user object
 */
if (isset($_POST['TC_SESSID'])) {
    session_id($_POST['TC_SESSID']);
    // dirty hack
    unset($_POST['TC_SESSID']);
    $_POST['data'] = $_POST;
}

session_start();
