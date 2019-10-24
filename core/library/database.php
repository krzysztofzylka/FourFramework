<?php
return $this->database = new class(){ 
	public $version = '1.0'; 
	public $conn; 
	public $isConnect = false; 
	public $connError = true; 
	public function connect(array $config){ 
		core::setError(); 
		if(!isset($config['type'])) 
			return core::setError(1, 'type configuration not found'); 
		try{ 
			switch($config['type']){ 
				case 'mysql':
					$check = $this->__checkConfig($config, ['host', 'name', 'user', 'password']); 
					if($check !== true) 
						return core::setError(4, 'no find config: '.$check); 
					$this->conn = new PDO('mysql:host='.$config['host'].';dbname='.$config['name'].'', $config['user'], $config['password']);
					break; 
				default:
					return core::setError(3, 'no find config: type'); 
					break; 
			}
			if(!is_object($this->conn)) 
				return core::setError(5, 'error connect to database'); 
			$this->isConnect = true; 
			$this->setCharset(); 
			return $this->conn; 
		}catch(PDOException $error){ 
			if($this->connError) 
				die('Error connect to database!<br />'.$error->getMessage()); 
			return core::setError(2, 'error connect to database', $error->getMessage()); 
		}
	}
	public function setCharset(string $name = 'utf8') : bool{ 
		core::setError(); 
		if(!$this->isConnect)
			return core::setError(1, 'connection error'); 
		$this->conn->exec("SET NAMES ".$name); 
		$this->conn->exec("SET CHARACTER SET ".$name); 
		return true;
	}
	private function __checkConfig(array $config, array $check){ 
		core::setError(); 
		foreach($check as $name) 
			if(!isset($config[$name])) 
				return $name; 
		return true; 
	}
}
?>