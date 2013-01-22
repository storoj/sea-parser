<?php
/**
 * Created by JetBrains PhpStorm.
 * User: storoj
 * Date: 14.01.13
 * Time: 7:31
 */

set_time_limit(0);
define('USE_DEBUG', 1);

include(dirname(__FILE__).'/init.php');

$parserClasses = array(
    'CParserMorvesti',
    'CParserInfranews',
    'CParserPortnews',
    'CParserSeanews'
);

foreach ($parserClasses as $parserClass) {
    echo "starting ".$parserClass."\n";

    $parser = CParserFactory::makeFromClassName($parserClass);
    for ($page=1; $parser->processPage($page); $page++) {
        echo "processing page #".$page."\n";
    }
}

