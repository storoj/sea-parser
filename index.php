<?php
/**
 * Created by JetBrains PhpStorm.
 * User: storoj
 * Date: 07.12.12
 * Time: 2:02
 */
define('USE_DEBUG', 1);

include(dirname(__FILE__).'/init.php');

$action = empty($_GET['action'])
    ? 'index'
    : $_GET['action'];
$action = ucfirst($action);

$query = empty($_GET['query'])
    ? array()
    : explode('/', $_GET['query']);
if (empty($query[count($query)-1])) {
    unset($query[count($query)-1]);
}

$className = 'CAction'.$action;
if (!class_exists($className)) {
    die('{"status":"error", "message":"Неопознанная команда"}');
}

$actionObj = new $className();
$result = $actionObj->getQueryResult($query);

echo json_encode($result);
