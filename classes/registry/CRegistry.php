<?php

abstract class CRegistry
{
    /**
     * @var CRegistry
     */
    private static $instance;
    protected $data = array();
    /**
     * @static
     * @return CRegistry
     */
    public static function getInstance()
    {
        $currentClass = get_called_class();
        if (!isset(self::$instance)) {
            self::$instance = array();
        }

        if (!isset(self::$instance[$currentClass])) {
            self::$instance[$currentClass] = new $currentClass();
        }

        return self::$instance[$currentClass];
    }

    protected  function __construct()
    {
        $this->data = $this->Load();
    }

    /**
     * @return mixed
     * loads data from storage
     */
    private function Load()
    {
        return $this->LoadData();
    }

    /**
     * saves data to storage
     */
    public function Save()
    {
        return $this->SaveData($this->data);
    }

    public function get($var, $default = NULL)
    {
        if(isset($this->data[$var])) {
            return $this->data[$var];
        }
        return $default;
    }

    public function set($var, $value)
    {
        $this->data[$var] = $value;
        return $this;
    }

    public function setArray($arData)
    {
        $this->data = array_merge($this->data, $arData);
        return $this;
    }

    public function remove($var)
    {
        if (isset($this->data[$var])) {
            unset($this->data[$var]);
        }
        return $this;
    }

    public function invalidate()
    {
        $this->data = array();
        return $this;
    }

    public function getKeyValue()
    {
        $data = array();
        foreach($this->data as $param => $value) {
            $data[$param] = $value;
        }
        return $data;
    }

    public function getData()
    {
        return $this->data;
    }

    protected abstract function LoadData();

    protected abstract function SaveData($arData);
}
