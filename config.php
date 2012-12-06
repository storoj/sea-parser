<?php
// DB settings
define('DB_PREFIX', 'parser_');
define('DB_NAME', 'parser');
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'solayma');

// path settings
define('PATH_ROOT', dirname(__FILE__).'/');
define('PATH_SITE_TEMPLATES', 'templates/');
define('PATH_CLASSES', PATH_ROOT.'classes/');
define('PATH_TEMPLATES', PATH_ROOT . PATH_SITE_TEMPLATES);
define('PATH_ENTITIES',  PATH_ROOT . 'entities/');
define('PATH_FILES', 'files/');
define('PATH_FILES_ABS',    PATH_ROOT . PATH_FILES);
define('PATH_FILES_TMP', PATH_FILES. 'tmp/');
define('PATH_FILES_TMP_ABS',    PATH_ROOT . PATH_FILES_TMP);

if(!defined('USE_DEBUG')){
    define('USE_DEBUG', false);
}

?>