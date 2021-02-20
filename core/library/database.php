<?php
return $this->database = new class(){ 
	public $version = '1.5'; 
	public $conn; 
	public $isConnect = false; 
	public $connError = true; 
	public $connType = null;
	private $connList = [];
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
				case 'sqlite':
					$check = $this->__checkConfig($config, ['path']);
					if($check !== true)
						return core::setError(4, 'no find config: '.$check);
					$this->conn = new PDO('sqlite:'.$config['path']);
					break;
				default:
					return core::setError(3, 'no find config: type'); 
					break; 
			}
			if(!is_object($this->conn)) 
				return core::setError(5, 'error connect to database'); 
			$this->isConnect = true; 
			$this->setCharset(); 
			$this->connType = $config['type'];
			array_push($this->connList, $this->conn);
			return $this->conn; 
		}catch(PDOException $error){ 
			if($this->connError) 
				die('Error connect to database!<br />'.$error->getMessage()); 
			return core::setError(2, 'error connect to database', $error->getMessage()); 
		}
	}
	public function setCharset(string $name = 'utf8', $conn = null) : bool{ 
		core::setError(); 
		if(!$this->isConnect) return core::setError(1, 'connection error');
		$conn = $conn??$this->conn;
		if(!$this->checkConnect($conn)) return core::setError(1, 'connection error');
		$conn->exec("SET NAMES ".$name); 
		$conn->exec("SET CHARACTER SET ".$name); 
		return true;
	}
	public function querySingleRow(string $sql, $conn = null){
		core::setError(); 
		if(!$this->isConnect) return core::setError(1, 'connection error');
		$conn = $conn??$this->conn;
		if(!$this->checkConnect($conn)) return core::setError(1, 'connection error');
		$prep = $this->prepare($sql, $conn);
		$prep->execute();
		return $prep->fetch(PDO::FETCH_ASSOC);
	}
	public function countRow(string $sTable, string $sWhere = null, $conn = null) : int{
		core::setError();
		if(!$this->isConnect) return core::setError(1, 'connection error');
		$conn = $conn??$this->conn;
		if(!$this->checkConnect($conn)) return core::setError(1, 'connection error');
		$where = $sWhere<>null?(' WHERE '.$sWhere):'';
		$prep = $this->prepare('SELECT count(*) as count FROM '.$sTable.''.$where, $conn);
		$prep->execute();
		$data = $prep->fetch(PDO::FETCH_ASSOC);
		return $data['count'];
	}
	private function __checkConfig(array $config, array $check){ 
		core::setError(); 
		foreach($check as $name) 
			if(!isset($config[$name])) 
				return $name; 
		return true; 
	}
	public function setConn(object $connObject) : bool{
		core::setError();
		if(!is_object($connObject))
			return core::setError(1, 'conn is not object');
		$this->isConnect = true;
		$this->conn = $connObject;
	}
	public function exec(string $query, $conn = null){
		core::setError();
		if(!$this->isConnect) return core::setError(1, 'connection error');
		$conn = $conn??$this->conn;
		if(!$this->checkConnect($conn)) return core::setError(1, 'connection error');
		return $conn->exec($query);
	}
	public function prepare(string $query, $conn = null){
		core::setError();
		if(!$this->isConnect) return core::setError(1, 'connection error');
		$conn = $conn??$this->conn;
		if(!$this->checkConnect($conn)) return core::setError(1, 'connection error');
		return $conn->prepare($query);
	}
	public function prepareSingleFunc(string $query, array $bindParam, $conn = null){
		core::setError();
		if(!$this->isConnect) return core::setError(1, 'connection error');
		$conn = $conn??$this->conn;
		if(!$this->checkConnect($conn)) return core::setError(1, 'connection error');
		$prep = $this->prepare($query, $conn);
		foreach($bindParam as $item)
			$prep->bindParam($item[0], $item[1]);
		return $prep->execute();
	}
	public function query(string $query, $conn = null){
		core::setError();
		if(!$this->isConnect) return core::setError(1, 'connection error');
		$conn = $conn??$this->conn;
		return $this->conn->query($query);
	}
	private function checkConnect($conn) : bool{
		if(!is_object($conn))
			return false;
		if(!($conn instanceof PDO))
			return false;
		return true;
	}
}
?>