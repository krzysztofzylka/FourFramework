<?php
return $this->database = new class(){ //create library
	public $version = '1.0'; //version
	public $conn; //pdo object
	public $isConnect = false; //is connect
	public $connError = true; //die if error
	public function connect(array $config){ //connect to database
		core::setError(); //clear error
		if(!isset($config['type'])) //config type
			return core::setError(1, 'type configuration not found'); //return error 1
		try{ //try
			switch($config['type']){ //switch type
				case 'mysql':
					$check = $this->__checkConfig($config, ['host', 'name', 'user', 'password']); //check config
					if($check !== true) //if error
						return core::setError(4, 'no find config: '.$check); //return error 4
					$this->conn = new PDO('mysql:host='.$config['host'].';dbname='.$config['name'].'', $config['user'], $config['password']);
					break; //break
				default:
					return core::setError(3, 'no find config: type'); //return error 3
					break; //break
			}
			if(!is_object($this->conn)) //check conn object
				return core::setError(5, 'error connect to database'); //return error 5
			$this->isConnect = true; //set isConnect
			$this->setCharset(); //set charset
			return $this->conn; //return pdo
		}catch(PDOException $error){ //if error
			if($this->connError) //if conn error
				die('Error connect to database!<br />'.$error->getMessage()); //die
			return core::setError(2, 'error connect to database', $error->getMessage()); //return error 2
		}
	}
	public function setCharset(string $name = 'utf8') : bool{ //set charset
		core::setError(); //clear error
		if(!$this->isConnect)
			return core::setError(1, 'connection error'); //return error 1
		$this->conn->exec("SET NAMES ".$name); //set names
		$this->conn->exec("SET CHARACTER SET ".$name); //set character
		return true;
	}
	private function __checkConfig(array $config, array $check){ //check config
		core::setError(); //clear error
		foreach($check as $name) //loop
			if(!isset($config[$name])) //check
				return $name; //return error (name)
		return true; //return true
	}
}
?>