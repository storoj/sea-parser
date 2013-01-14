<?php
/**
 * Created by JetBrains PhpStorm.
 * User: storoj
 * Date: 14.01.13
 * Time: 3:52
 */

abstract class CAction
{
    protected $postData = array();
    private $status = '';
    private $message = '';
    private $result = null;

    public function __construct()
    {
        $this->postData = $_POST;
    }

    public function getQueryResult($query)
    {
        $this->executeQuery($query);
        return $this->result;
    }

    private function executeQuery($query)
    {
        $paramCount = count($query);

        for($i=$paramCount; $i>0; $i--){
            $methodName = implode('_', array_slice($query, 0, $i));
            $methodParams = array_slice($query, $i);

            if($this->tryAction($methodName, $methodParams)){
                return;
            }
        }

        $this->tryAction('index', $query);
    }

    private function tryAction($action, $params)
    {
        $methodName = 'action_'.$action;
        if(method_exists($this, $methodName)){
            $this->result = call_user_func_array(array($this, $methodName), array($params));
            return true;
        }

        return false;
    }

    protected function setStatus($status, $msg = '')
    {
        $this->status = $status;
        $this->message = $msg;
    }
}
