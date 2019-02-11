<?php
return $this->database = new class($this->core){
	private $core;
	public $conn;
	public $is_connect = false;
	public $advanced_logs = false; //write all sql script to log
	public function __construct($obj){
		$this->core = $obj;
	}
	//connect to database
	public function connect(array $config){
		$this->core->returnError();
		if(!isset($config['type'])) return false;
		try{
			//array: charset
			switch($config['type']){
				//type, host, name, login, password
				case 'mysql':
					if(!isset($config['host']) or !isset($config['name']) or !isset($config['login']) or !isset($config['password'])) return $this->core->returnError(1, 'you must configurate connection'); //error 1
					$this->conn = new PDO("mysql:host=".$config['host'].";dbname=".$config['name'], $config['login'], $config['password']);
					break;
				//sqlite
				case 'sqlite':
					if(!isset($config['sqlite'])) return $this->core->returnError(1, 'you must configurate connection'); //error 1
					$this->conn = new PDO("sqlite:".$config['sqlite']);
					break;
				//host, port, name, login, password
				case 'postgresql':
					if(!isset($config['host']) or !isset($config['name']) or !isset($config['port']) or !isset($config['login']) or !isset($config['password'])) return $this->core->returnError(1, 'you must configurate connection'); //error 1
					$this->conn = new PDO("pgsql:host=".$config['host'].";port=".$config['port'].";dbname=".$config['name'].";user=".$config['login'].";password=".$config['password']);
					break;
				//name, login, password
				case 'oracle':
					if(!isset($config['name']) or !isset($config['login']) or !isset($config['password'])) return $this->core->returnError(1, 'you must configurate connection'); //error 1
					$this->conn = new PDO("oci:dbname=".$config['name'],$config['login'],$config['password']);
					break;
			}
			//if success
			if(is_object($this->conn)){
				$this->is_connect = true;
				if(isset($config['charset'])) $this->setCharset();
				$this->core->wlog('Success connect to '.$config['type'].' database', 'database', 'message');
				return $this->conn;
			}
			$this->core->wlog('Error connect to database', 'library database', 'error');
			return $this->core->returnError(2, 'error connect to database'); //error 2
		}catch(PDOException $error){
			$this->core->wlog('Error DB Connection: '.$error->getMessage(), 'library database', 'error');
			return $this->core->returnError(3, 'error connect to database', $error->getMessage()); //error 3
		}
	}
	//set character
	public function setCharset(string $name = 'utf8') : bool{
		$this->core->returnError();
		if(!$this->is_connect) return $this->core->returnError(1, 'connection error'); //error 1
		//charset
		$this->conn->exec("SET NAMES ".$name);
		$this->conn->exec("SET CHARACTER SET ".$name);
		return true;
	}
	//lastInsertId
	public function lastInsertId(){
		$this->core->returnError();
		if(!$this->is_connect) return $this->core->returnError(1, 'connection error'); //error 1
		return $this->conn->lastInsertId();
	}
	//query
	public function query(string $sql){
		$this->core->returnError();
		if(!$this->is_connect) return $this->core->returnError(1, 'connection error'); //error 1
		$this->_log($sql);
		return $this->conn->query($sql);
	}
	//exec
	public function exec(string $sql){
		$this->core->returnError();
		if(!$this->is_connect) return $this->core->returnError(1, 'connection error'); //error 1
		$this->_log($sql);
		return $this->conn->exec($sql);
	}
	//prepare
	public function prepare(string $sql, array $option = array()){
		$this->core->returnError();
		if(!$this->is_connect) return $this->core->returnError(1, 'connection error'); //error 1
		$this->_log($sql);
		return $this->conn->prepare($sql, $option);
	}
	//logi informacyjne dla testów
	private function _log($sql){
		$this->core->returnError();
		if(!$this->advanced_logs) return false;
		$this->core->wlog('SQL: '.$sql, 'library database advenced', 'message');
	}
}
?>