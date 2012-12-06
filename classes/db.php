<?php

class DB {
	private static $instance;
	private $tablePrefix	= '';
	private $host			= 'localhost';
	private $user			= '';
	private $password		= '';
	private $dbName			= '';
    /**
     * @var bool|Memcached
     */
    private $cache 			= false;
    public $connected       = false;

    /**
     * @static
     * @return DB
     */
    public static function getInstance() {
		if (!isset(self::$instance)) {
			self::$instance = new DB;
		}

		return self::$instance;
	}
	
	public function tryCache(){
		if($this->cache !== false){
			return true;
		}
		if(!class_exists('Memcached')){
			return false;
		}
		$this->cache = new Memcached;
		return $this->cache->addServer('localhost', 11211);
	}
	
	public function getCached($key) {
		if($this->tryCache()){
			return $this->cache->get($key);
		}
		return false;
	}
	
	public function setCached($key, $value, $timeout = 3) {
		if($this->tryCache()){
			return $this->cache->set($key, $value, $timeout);
		}
		return false;
	}

    public function init($host, $user, $password, $db, $prefix) {
        $this->host = $host;
        $this->user = $user;
        $this->password = $password;
        $this->dbName = $db;
        $this->tablePrefix = $prefix;

        $this->connected = false;
    }

	public function connect() {
		if (mysql_connect($this->host, $this->user, $this->password) == false) {
			return false;
		}

		if (!mysql_select_db($this->dbName)) {
			return false;
		}
        $this->connected = true;

        $this->query("set names utf8");

		return true;
	}

	public function getFullTableName($tableName) {
		return $this->tablePrefix . $tableName;
	}

	public function query($sql) {
        if (!$this->connected && !$this->connect()) {
            echo "unable to connect to db\n";
            die();
        }

        $start = false;
		if (USE_DEBUG) {
			$start = Debugger::instance()->getMicroTime();
		}

		$res = mysql_query($sql);

		if (USE_DEBUG) {
            $trace = debug_backtrace();
            for($i=0; $i<count($trace) && (!isset($trace[$i]['file']) || false === strpos($trace[$i]['file'], DIRECTORY_SEPARATOR . 'page.')); ++$i);

            $file_info = '';
            if (isset($trace[$i])){
                $trace = $trace[$i];
                $file_info = '#'.$trace['line'].' '.basename($trace['file']);
            }

			$end = Debugger::instance()->getMicroTime();

            $result = @mysql_num_rows($res);
            if (!$result) {
                $result = @mysql_error();
            }
			Debugger::instance()->addRequest($sql, ($end - $start), $result, $file_info);
		}

		return $res;
	}

    public function getPrefix(){
        return $this->tablePrefix;
    }

	public function insertID(){
		return mysql_insert_id();
	}

}
