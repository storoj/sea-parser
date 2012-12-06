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

if(!defined('USE_DEBUG')){
    define('USE_DEBUG', false);
}

?>