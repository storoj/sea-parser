<?php
/**
 * Created by JetBrains PhpStorm.
 * User: storoj
 * Date: 14.01.13
 * Time: 4:26
 */

class CRegistryJSON extends CRegistry
{
    /**
     * @var string|null
     */
    private $settingsFileName = null;

    public function __construct()
    {
        $this->settingsFileName = PATH_ROOT . 'settings.json';
        parent::__construct();
    }

    protected function LoadData()
    {
        if (file_exists($this->settingsFileName)) {
            $json = json_decode(file_get_contents($this->settingsFileName), true);
            if (!is_array($json)) {
                $json = array();
            }
            return $json;
        }
        return array();
    }

    protected function SaveData($arData)
    {
        if (!is_array($arData) && !is_object($arData)) {
            $arData = array();
        }
        $json = json_encode($arData);
        return file_put_contents($this->settingsFileName, $json);
    }
}