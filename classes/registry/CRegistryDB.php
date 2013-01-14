<?php
/**
 * Created by JetBrains PhpStorm.
 * User: storoj
 * Date: 14.01.13
 * Time: 4:26
 */

class CRegistryDB extends CRegistry
{
    protected function LoadData()
    {
        $query = DBQuery::withTable('settings')
            ->table('settings')
            ->getFields('*');
        return $query->fetchAll('param');
    }

    protected function SaveData($arData)
    {
        $query = DBQuery::withTable('settings');
        foreach($arData as $param => $paramData){
            $query
                ->setFields(array('value' => $paramData['value']))
                ->where(array('param' => $param))
                ->Update();
        }
    }
}