<?php
return $this->database = new class($this->core){
	private $core;
	public $conn;
	public $version = '1.0';
	public $is_connect = false;
	public $advanced_logs = false;
	public $connError = true;
	public function __construct($obj){
		$this->core = $obj;
	}
	public function connect(array $config){
		$this->core->returnError();
		if(!isset($config['type']))
			return false;
		try{
			switch($config['type']){
				case 'mysql':
					if(!isset($config['host']) or !isset($config['name']) or !isset($config['login']) or !isset($config['password']))
						return $this->core->returnError(1, 'you must configurate connection');
					$this->conn = new PDO("mysql:host=".$config['host'].";dbname=".$config['name'], $config['login'], $config['password']);
					break;
				case 'sqlite':
					if(!isset($config['sqlite']))
						return $this->core->returnError(1, 'you must configurate connection');
					$this->conn = new PDO("sqlite:".$config['sqlite']);
					break;
				case 'postgresql':
					if(!isset($config['host']) or !isset($config['name']) or !isset($config['port']) or !isset($config['login']) or !isset($config['password']))
						return $this->core->returnError(1, 'you must configurate connection');
					$this->conn = new PDO("pgsql:host=".$config['host'].";port=".$config['port'].";dbname=".$config['name'].";user=".$config['login'].";password=".$config['password']);
					break;
				case 'oracle':
					if(!isset($config['name']) or !isset($config['login']) or !isset($config['password']))
						return $this->core->returnError(1, 'you must configurate connection');
					$this->conn = new PDO("oci:dbname=".$config['name'],$config['login'],$config['password']);
					break;
			}
			if(is_object($this->conn)){
				$this->is_connect = true;
				if(isset($config['charset']))
					$this->setCharset();
				$this->core->wlog('Success connect to '.$config['type'].' database', 'database', 'message');
				return $this->conn;
			}
			$this->core->wlog('Error connect to database', 'library database', 'error');
			return $this->core->returnError(2, 'error connect to database');
		}catch(PDOException $error){
			$this->core->wlog('Error DB Connection: '.$error->getMessage(), 'library database', 'error');
			if($this->connError)
				die('Error connect to database!');
			return $this->core->returnError(3, 'error connect to database', $error->getMessage());
		}
	}
	public function setCharset(string $name = 'utf8') : bool{
		$this->core->returnError();
		if(!$this->is_connect)
			return $this->core->returnError(1, 'connection error');
		$this->conn->exec("SET NAMES ".$name);
		$this->conn->exec("SET CHARACTER SET ".$name);
		return true;
	}
	public function lastInsertId(){
		$this->core->returnError();
		if(!$this->is_connect)
			return $this->core->returnError(1, 'connection error');
		return $this->conn->lastInsertId();
	}
	public function query(string $sql){
		$this->core->returnError();
		if(!$this->is_connect)
			return $this->core->returnError(1, 'connection error');
		$this->_log($sql);
		return $this->conn->query($sql);
	}
	public function exec(string $sql){
		$this->core->returnError();
		if(!$this->is_connect)
			return $this->core->returnError(1, 'connection error');
		$this->_log($sql);
		return $this->conn->exec($sql);
	}
	public function prepare(string $sql, array $option = array()){
		$this->core->returnError();
		if(!$this->is_connect)
			return $this->core->returnError(1, 'connection error');
		$this->_log($sql);
		return $this->conn->prepare($sql, $option);
	}
	private function _log(string $sql){
		$this->core->returnError();
		if(!$this->advanced_logs)
			return false;
		$this->core->wlog('SQL: '.$sql, 'library database advenced', 'message');
	}
	public function __debugInfo() : array{
		return [
			'version' => $this->version,
			'conn' => $this->conn,
			'is_connect' => $this->is_connect,
			'advanced_logs' => $this->advanced_logs,
			'connError' => $this->connError,
		];
	}
}
?>