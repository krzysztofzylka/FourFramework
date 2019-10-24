<?php
return $this->db = new class(){ 
	public $version = '1.0.4'; 
	public $tableVersion = '1.1'; 
	public $path = ''; 
	private $connection = []; 
	private $_regexp = [
		'fileName' => '([a-zA-Z0-9\-\_\!\@\#\$\%\^\&\(\)\=]+)',
		'getData' => '[\'|"|`]?([a-zA-Z0-9]+)[\'|"|`]? ?= ?([\'|"|`]?(.+?)[\'|"|`]?|([a-zA-Z0-9\"\']+))[,|\n]', 
		'createTableData' => '[\'|\"|\`](.+?)[\'|\"|\`] ((int|string|bool)(\([0-9]+\))|text)[ |,]?(autoincrement)?', 
		'addData' => '[\'|\"|\`]?(.+?)[\'|\"|\`]?, ?', 
		'request' => [
			'createTable' => '(CREATE TABLE) [\'|\"|\`]?([a-zA-Z0-9]+)[\'|\"|\`]? {(.+)}',
			'addDataTo' => '(ADD DATA TO) [\'|"|`]?{$fileName}[\'|"|`]? \((.+)\) VALUES \((.+)\)',
			'select' => '(SELECT) [\'|"|`]?(.+?)?[\'|"|`]? ?(FROM) ["|\'|`]?{$fileName}["|\'|`]? ?(WHERE (.+))?',
			'advenced' => '(ADVENCED) (GET|SET) (.+) FROM ["|\'|`]?{$fileName}["|\'|`]? ?(OPTION+ ?(.+))?',
			'advencedGet' => '(ADVENCED) (GET) ["|\'|`](.+)["|\'|`]',
			'updateWhere' => '(UPDATE) ["|\'|`]?{$fileName}["|\'|`]? SET (.+) WHERE (.+)',
			'update' => '(UPDATE) ["|\'|`]?{$fileName}["|\'|`]? SET (.+)'
		]
	];
	private $activeConnect = null; 
	private $debug = true;
	public function __construct(){ 
		core::setError(); 
		$this->path = core::$path['base'].'db/'; 
		if(!file_exists($this->path))
			mkdir($this->path, 0700, true); 
		foreach($this->_regexp['request'] as $name => $data) 
			$this->_regexp['request'][$name] = preg_replace_callback('/{\$([a-zA-Z0-9]+)}/ms', 'self::___replaceRegexp', $this->_regexp['request'][$name]); 
	}
	public function request(string $script, string $connect = null){ 
		core::setError(); 
		$connect = $connect??array_keys($this->connection)[0]; 
		if($this->___checkConnect($connect) == false) 
			return core::setError(1, 'connection error'); 
		foreach($this->_regexp['request'] as $regexp){ 
			preg_match_all('/'.$regexp.'/ms', $script, $matches, PREG_SET_ORDER, 0); 
			if(count($matches) > 0){ 
				$this->activeConnect = $connect; 
				$request = $this->_request($matches[0]); 
				$this->activeConnect = null; 
				return $request; 
			}
		}
	}
	public function connect(string $name, string $password = null){ 
		$name = basename(htmlspecialchars($name)); 
		$path = $this->path.$name.'/'; 
		if(!file_exists($path)) 
			return core::setError(1, 'database is not exists'); 
		$pathPass = $path.'passwd.php'; 
		if(!file_exists($pathPass)) 
			return core::setError(2, 'pass file is not exists'); 
		$passMD5 = include($pathPass); 
		if($passMD5 <> md5($password)) 
			return core::setError(3, 'password incorect'); 
		$connArray = [
			'name' => $name, 
			'path' => $path, 
			'pass' => md5($password) 
		]; 
		$uniqueID = core::$library->string->generateString(10, [true, true, false, false]); 
		$this->connection[$uniqueID] = $connArray; 
		return $uniqueID; 
	}
	public function createDatabase(string $name, string $password = null){ 
		$name = basename(htmlspecialchars($name)); 
		$path = $this->path.$name.'/'; 
		if(file_exists($path)) 
			return core::setError(1, 'database is already exists'); 
		mkdir($path, 0700, true); 
		$pathPass = $path.'passwd.php'; 
		file_put_contents($pathPass, '<?php return "'.md5($password).'"; ?>'); 
		return true; 
	}
	private function _request($matches){ 
		$this->___debug($matches, '_request'); 
		switch($matches[1]){
			case "CREATE TABLE":
				preg_match_all('/'.$this->_regexp['createTableData'].'/ms', $matches[3], $matchesData, PREG_SET_ORDER, 0);
				return $this->__createTable($matches[2], $matchesData);
				break;
			case "ADD DATA TO":
				preg_match_all('/'.$this->_regexp['addData'].'/s', $matches[3].',', $matchesDataColumn, PREG_SET_ORDER, 0);
				preg_match_all('/'.$this->_regexp['addData'].'/s', $matches[4].',', $matchesData, PREG_SET_ORDER, 0);
				return $this->__addData($matches[2], $matchesDataColumn, $matchesData);
				var_dump($matches);
				break;
			case "SELECT":
				return $this->__selectData($matches[4], isset($matches[6])?$matches[6]:null);
				break;
			case "UPDATE":
				preg_match_all('/'.$this->_regexp['getData'].'/ms', $matches[3].',', $matchesSet, PREG_SET_ORDER, 0);
				return $this->__update($matches[2], $this->___getArrayData($matchesSet, 2), isset($matches[4])?$matches[4]:null);
				break;
			case "ADVENCED":
				switch($matches[2]){
					case 'GET':
						switch($matches[3]){
							case 'tableList':
								$path = $this->connection[$this->activeConnect]['path'];
								$scandir = glob($path.'*.{fdb}', GLOB_BRACE);
								foreach($scandir as $id => $name){
									$explode = explode('/', $name);
									$scandir[$id] = str_replace('.fdb', '', $explode[count($explode)-1]);
								}
								return $scandir;
								break;
							case 'column':
								$read = $this->____readFile($matches[4]);
								return $read['column'];
								break;
							case 'autoincrement':
								$read = $this->____readFile($matches[4]);
								return $read['option']['autoincrement'];
								break;
						}
						break;
					case 'SET':
						break;
				}
				break;
		}
	}
	private function __createTable(string $name, array $data){ 
		core::setError(); 
		$name = htmlspecialchars(basename($name)); 
		$this->___debug($name, 'name'); 
		$DBpath = $this->connection[$this->activeConnect]['path'].$name.'.fdb'; 
		$this->___debug($DBpath, 'DBpath'); 
		if(file_exists($DBpath)) 
			return core::setError(101, 'error create table', 'table is already exists'); 
		$table = [ 
			'option' => [ 
				'name' => $name, 
				'version' => $this->tableVersion, 
				'autoincrement' => [ 
					'ai' => false, 
					'id' => -1, 
					'count' => 1 
				]
			],
			'column' => [
				'count' => 0 
			],
			'data' => [] 
		];
		$column = []; 
		foreach($data as $item){ 
			if(count($item) < 5) 
				return core::setError(100, 'error create table', 'error generate column'); 
			$column = [
				'name' => (string)$item[1], 
				'type' => (string)$item[3], 
				'length' => (int)str_replace(['(', ')'], ['', ''], $item[4]) 
			];
			if(isset($item[5]) and $item[5] == 'autoincrement'){ 
				$table['option']['autoincrement']['ai'] = true; 
				$table['option']['autoincrement']['id'] = $table['column']['count']; 
			}
			$table['column'][$table['column']['count']] = $column; 
			$table['column']['count']++; 
		}
		$this->___debug($table, 'table'); 
		return $this->____saveFile($name, $table); 
	}
	private function __addData(string $tableName, array $column, array $data){ 
		$tableName = htmlspecialchars(basename($tableName)); 
		$column = $this->___getArrayData($column); 
		$data = $this->___getArrayData($data); 
		if(count($column) <> count($data)) 
			return core::setError(50, 'data count error'); 
		$this->___debug($tableName, 'tableName'); 
		$this->___debug($column, 'column'); 
		$this->___debug($data, 'data'); 
		$readFile = $this->____readFile($tableName); 
		$newData = []; 
		$autoincrement = $readFile['option']['autoincrement']; 
		foreach($readFile['column'] as $id => $item){ 
			if(is_array($item)){ 
				$search = array_search($item['name'], $column); 
				if(is_int($search)) 
					$newData[$column[$search]] = $data[$search]; 
				if($autoincrement['ai'] == true and $item['name'] == $readFile['column'][$autoincrement['id']]['name']){ 
					$newData[$item['name']] = $autoincrement['count']; 
					$readFile['option']['autoincrement']['count']++; 
					$search = -1; 
				}
				if(is_bool($search)) 
					return core::setError(51, 'error search column', 'error find column \''.$item['name'].'\''); 
				$columnName = $search==-1?$readFile['column'][$autoincrement['id']]['name']:$column[$search];
				switch($item['type']){ 
					case 'int': 
						$item['type'] = 'integer'; 
						break;
				}
				if(gettype($newData[$item['name']]) <> $item['type']) 
					return core::setError(52, 'error data type', 'column: '.$item['name'].', type: '.$columnType.' ('.gettype($dane).')'); 
				if(strlen($newData[$item['name']]) > $item['length'])
					return core::setError(53, 'error data length', 'column: '.$item['name'].', length: '.strlen($newData[$item['name']]).'/'.$item['length']); 
			}
		}
		array_push($readFile['data'], $newData); 
		$this->____saveFile($tableName, $readFile); 
		return true;
	}
	private function __selectData(string $tableName, string $where=null){
		$tableName = htmlspecialchars(basename($tableName)); 
		$readFile = $this->____readFile($tableName); 
		$data = $readFile['data']; 
		if($where <> null){ 
			$data = $this->__search($data, $where); 
			$data = array_values(array_filter($data)); 
		}
		return $data; 
	}
	private function __update(string $tableName, array $set, string $where = null){
		$tableName = htmlspecialchars(basename($tableName)); 
		$readFile = $this->____readFile($tableName); 
		$data = $readFile['data']; 
		if($where <> null) 
			$data = $this->__search($data, $where); 
		$data = array_keys($data);
		foreach($data as $key)
			foreach($set as $columnName => $columnText)
				$readFile['data'][$key][$columnName] = $columnText;
		$this->____saveFile($tableName, $readFile); 
		return true;
	}
	private function __search(array $data, string $search) : array{ 
		$explode = explode(' and ', $search); 
		foreach($explode as $item){ 
			$exp = $this->___searchExplode($item, '='); 
			if($exp <> false){ 
				foreach($data as $dataID => $dataData){ 
					if($dataData[$exp[0]] <> $exp[1]) 
						unset($data[$dataID]); 
				}
			}
			$exp = $this->___searchExplode($item, '%'); 
			if($exp <> false){ 
				foreach($data as $dataID => $dataData){ 
					if(core::$library->string->strpos($dataData[$exp[0]], $exp[1]) == -1) 
						unset($data[$dataID]); 
				}
			}
		}
		return $data;
	}
	private function __advenced(string $tableName, array $data){
		
	}
	private function ___checkTable(string $tableName){
		
	}
	private function ___replaceRegexp($matches){ 
		return $this->_regexp[$matches[1]];
	}
	private function ___checkConnect($uniqueID){ 
		if(!isset($this->connection[$uniqueID])) 
			return false;
		if(!is_array($this->connection[$uniqueID])) 
			return false;
		return true;
	}
	private function ___debug($data, $title=''){ 
		if($this->debug)
			core::$library->debug->consoleLog($data, $title); 
	}
	private function ___getArrayData($array, $type=1){ 
		switch($type){
			case 1: 
				foreach($array as $id=>$data)
					$array[$id] = $data[1];
				break;
			case 2: 
				foreach($array as $id=>$data){
					$array[$data[1]] = $data[3];
					unset($array[$id]);
				}
				break;
		}
		return $array;
	}
	private function ___searchExplode($string, $exp){ 
		$explode = explode($exp, $string, 2); 
		if($explode[0] == $string)
			return false; 
		return $explode; 
	}
	private function ____saveFile(string $tableName, array $data){ 
		$tablePath = $this->connection[$this->activeConnect]['path'].$tableName.'.fdb'; 
		$data['column'] = $this->____crypt($data['column']); 
		$data['data'] = $this->____crypt($data['data']); 
		$data['option']['autoincrement'] = $this->____crypt($data['option']['autoincrement']); 
		$data['option'] = json_encode($data['option']); 
		$writeFile = $data['option'].PHP_EOL 
					.$data['column'].PHP_EOL 
					.$data['data']; 
		file_put_contents($tablePath, $writeFile); 
	}
	private function ____readFile(string $tableName){ 
		$tablePath = $this->connection[$this->activeConnect]['path'].$tableName.'.fdb'; 
		$readFile = file($tablePath); 
		$readFile = [
			'option' => json_decode($readFile[0], true), 
			'column' => $this->____decrypt($readFile[1]), 
			'data' => $this->____decrypt($readFile[2]), 
		];
		$readFile['option']['autoincrement'] = $this->____decrypt($readFile['option']['autoincrement']); 
		return $readFile; 
	}
	private function ____crypt($data){ 
		$password = $this->connection[$this->activeConnect]['pass']; 
		if(is_array($data)) 
			$data = json_encode($data); 
		return core::$library->crypt->crypt($data, $password); 
	}
	private function ____decrypt(string $data){ 
		$password = $this->connection[$this->activeConnect]['pass']; 
		$data = core::$library->crypt->decrypt($data, $password); 
		return json_decode($data, true); 
	}
}
?>