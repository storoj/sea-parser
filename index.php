<?php
/**
 * Created by JetBrains PhpStorm.
 * User: storoj
 * Date: 07.12.12
 * Time: 2:02
 */
set_time_limit(0);
define('USE_DEBUG', 1);

include(dirname(__FILE__).'/init.php');


//$parserClass = 'CParserInfranews';
//$parserClass = 'CParserSeanews';
$parserClass = 'CParserMorvesti';

$parser = CParserFactory::makeFromClassName($parserClass);
for ($page=3; $page<10; $page++) {
    echo "processing page #".$page."\n";
    $parser->processPage($page);
}
//echo Debugger::instance()->plainTextOutput();