<?php
/**
 * Debugger
 */

class Debugger {
    private static $_instance;

    private $debug_data;
    private $start;
    private $end;
    public $state = '';
    public $enabled = true;

    public static function instance() {
        if (!isset(self::$_instance)) {
            self::$_instance = new Debugger();
        }

        return self::$_instance;
    }

    private function __construct() {
        $this->sql_dump = array();
    }

    public function getMicroTime() {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    public function startDebug() {
        $this->start = $this->getMicroTime();
    }

    public function endDebug() {
        $this->end = $this->getMicroTime();
    }

    public function addRequest($sql, $time = 0, $result = NULL, $file_info = '') {
        $this->debug_data[] = array('sql' => $sql, 'time' => ($time * 1000), 'result' => $result, 'file_info' => $file_info);
        // add time count
    }

    public function addClass($class) {
        $this->debug_data[] = array('class' => $class);
    }

    public function addError($error) {
        $this->debug_data[] = array('error' => $error);
    }

    public function showDebugInfo($path = null) {
        if(is_null($path)){
            $path = PATH_ROOT;
        }
        if(!isset($_COOKIE['debug_panel_state'])) $_COOKIE['debug_panel_state'] = '';
        $panel = file_get_contents($path.'debugger/debugger.html');
        $panel = str_replace("{state}", $_COOKIE['debug_panel_state'], $panel);
        $content = '<p class="debug_exec_time">Full execution time: <b class="debug_table">'.$this->getExecTime().'</b> msec</p>';

        $content .= implode("\n", $this->getRawData());

        return str_replace('%content%', $content, $panel);
    }

    public function getRawData() {
        $data = array();
        foreach($this->debug_data as $el){
            $row = '<p>';
            if (array_key_exists('sql', $el)) {
                // light up queries
                $request = preg_replace('/("[\w]*?")/', '<span class="debug_value">$1</span>', $el['sql']);
                $request = preg_replace('/(`[\w]*?`)/', '<span class="debug_table">$1</span>', $request);
                $row .= '<span class="debug_def">[Q]</span> :: '
                    .$request.'&nbsp;&nbsp;$$$&nbsp;&nbsp;exec time: '
                    .number_format($el['time'], 5).' msec&nbsp;&nbsp;$$$&nbsp;&nbsp;'.$el['result'].' rows';
            } elseif (array_key_exists('class', $el)) {
                $row .= '<span class="debug_def">[C]</span> :: Loading class &gt;&gt; <span class="debug_class">'.$el['class'].'</span>';
            } elseif (array_key_exists('error', $el)) {
                $row .= '<span class="debug_def">[E]</span> :: Error occured &gt;&gt; <span class="debug_error">'.$el['error'].'</span>';
            }

            if(isset($el['file_info'])){
                $row .= ' <span class="debug_file_info">'.$el['file_info'].'</span>';
            }
            $row .= '</p>';
            $data[] = $row;
        }

        return $data;
    }

    public function plainTextOutput()
    {
        $output = '';
        foreach($this->debug_data as $el){
            $row = '';
            if (array_key_exists('sql', $el)) {
                // light up queries
                $request = $el['sql'];
                $row .= '[Q] :: '
                    .$request.' $$$ exec time: '
                    .number_format($el['time'], 5).' msec $$$ '.$el['result'].' rows';
            } elseif (array_key_exists('class', $el)) {
                $row .= '[C] :: Loading class >> '.$el['class'];
            } elseif (array_key_exists('error', $el)) {
                $row .= '[E] :: Error occured >> '.$el['error'];
            }

            if(isset($el['file_info'])){
                $row .= ' '.$el['file_info'];
            }
            $row .= "\n";
            $output .= $row;
        }

        return $output;
    }

    public function getExecTime() {
        return number_format(($this->end - $this->start) * 1000, 5);
    }
}