<?php

class Registry {
    /**
     * @var Registry
     */
    private static $instance;
    private $dbData = array();
    /**
     * @static
     * @return Registry
     */
    public static function getInstance() {
        if (!isset(self::$instance)) {
            self::$instance = new Registry();
        }

        return self::$instance;
    }

    private function __construct(){
        $query = DBQuery::withTable('settings')
            ->table('settings')
            ->getFields('*');
        $this->dbData = $query->fetchAll('param');
    }

    public function get($var, $default = NULL){
        if(isset($this->dbData[$var])){
            return $this->dbData[$var]['value'];
        }
        return $default;
    }

    public function set($var, $value){
        $this->dbData[$var]['value'] = $value;
        return $this;
    }

    public function setArray($arData){
        $this->dbData = array_merge($this->dbData, $arData);
        return $this;
    }

    public function Save(){
        $query = DBQuery::withTable('settings');
        foreach($this->dbData as $param => $paramData){
            $query
                ->setFields(array('value' => $paramData['value']))
                ->where(array('param' => $param))
                ->Update();
        }
    }

    public function getKeyValue(){
        $data = array();
        foreach($this->dbData as $param => $value){
            $data[$param] = $value['value'];
        }
        return $data;
    }

    public function getSettings(){
        return $this->dbData;
    }
}