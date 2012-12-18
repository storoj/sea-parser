<?php
/**
 * Created by JetBrains PhpStorm.
 * User: storoj
 * Date: 07.12.12
 * Time: 1:57
 */

class CParserFactory {

    /**
     * @param $className
     * @return CAParser
     */
    public static function makeFromClassName($className)
    {
        return new $className();
    }

}