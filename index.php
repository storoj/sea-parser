<?php
/**
 * Created by JetBrains PhpStorm.
 * User: storoj
 * Date: 07.12.12
 * Time: 2:02
 */

include(dirname(__FILE__).'/init.php');

$infraNewsParser = new CParserInfranews();
for ($page=1; $page<685; $page++) {
    $infraNewsParser->processPage($page);
}