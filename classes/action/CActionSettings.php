<?php
/**
 * Created by JetBrains PhpStorm.
 * User: storoj
 * Date: 14.01.13
 * Time: 4:05
 */

class CActionSettings extends CAction
{
    public function action_index($arParams = array())
    {
        echo 'action_index';
        print_r($arParams);
    }

    public function action_list($arParams = array())
    {
        echo 'action_list';
        print_r($arParams);
    }
}