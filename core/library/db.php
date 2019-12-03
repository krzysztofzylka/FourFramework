<?php
return $this->db = new class(){ 
	public $version = '1.0.7a';
	public $tableVersion = '1.1'; 
	public $path = ''; 
	private $connection = []; 
	private $_regexp = [
		'fileName' => '([a-zA-Z0-9\-\_\!\@\#\$\%\^\&\(\)\=]+)',
		'getData' => '[\'|"|`]?([a-zA-Z0-9]+)[\'|"|`]? ?= ?([\'|"|`]?(.+?)[\'|"|`]?|([a-zA-Z0-9\"\']+))[,|\n]', 
		'createTableData' => '[\'|\"|\`](.+?)[\'|\"|\`] ((int|string|bool)(\([0-9]+\))|text)[ |,]?(autoincrement)?', 
		'addData' => '[\'|\"|\`]?(.+?)[\'|\"|\`]?, ?', 
		'search' => '[\'|"|`]?([a-zA-Z0-9]+)[\'|"|`]?(=|%)[\'|"|`]?([a-zA-Z0-9]+)[\'|"|`]?',
		'request' => [
			'createTable' => '(CREATE TABLE) [\'|\"|\`]?{$fileName}[\'|\"|\`]? {(.+)}',
			'addDataTo' => '(ADD DATA TO) [\'|"|`]?{$fileName}[\'|"|`]? \((.+)\) VALUES \((.+)\)',
			'select' => '(SELECT) [\'|"|`]?(.+?)?[\'|"|`]? ?FROM ["|\'|`]?{$fileName}["|\'|`]? ?(WHERE (.+))?',
			'advenced' => '(ADVENCED) (GET|SET) (.+) FROM ["|\'|`]?{$fileName}["|\'|`]? ?(OPTION+ ?(.+))?',
			'advencedGet' => '(ADVENCED) (GET) ["|\'|`](.+)["|\'|`]',
			'updateWhere' => '(UPDATE) ["|\'|`]?{$fileName}["|\'|`]? SET (.+) WHERE (.+)',
			'update' => '(UPDATE) ["|\'|`]?{$fileName}["|\'|`]? SET (.+)',
			'delete' => '(DELETE) FROM [\'|\"|\`]?{$fileName}[\'|\"|\`]? ?(WHERE)? ?(.+)?',
		]
	];
	private $activeConnect = null; 
	public function __construct(){ 
		core::setError(); 
		$this->path = core::$path['base'].'db/'; 
		if(!file_exists($this->path))
			mkdir($this->path, 0700, true); 
		//regexp replace
		foreach($this->_regexp['request'] as $name => $data){
			foreach($this->_regexp as $kName => $kData)
				if(!is_array($kData))
					$data = str_replace('{$'.$kName.'}', $kData, $data);
			$this->_regexp['request'][$name] = $data;
		}
	}
	public function request(string $script, string $connect = null){ 
		core::setError();
		$connect = $connect??array_keys($this->connection)[0]; 
		if(!isset($this->connection[$connect]) or !is_array($this->connection[$connect])) 
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
		return core::setError(2, 'the script is invalid');
	}
	public function connect(string $name, string $password = null){ 
		core::setError();
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
		$uniqueID = core::$library->string->generateString(10, [true, true, false, false]);
		$this->connection[$uniqueID] = [
			'name' => $name, 
			'path' => $path, 
			'pass' => md5($password) 
		];
		return $uniqueID; 
	}
	public function createDatabase(string $name, string $password = null){ 
		core::setError();
		$path = $this->path.basename(htmlspecialchars($name)).'/'; 
		if(file_exists($path)) 
			return core::setError(1, 'database is already exists'); 
		mkdir($path, 0700, true);
		file_put_contents($path.'passwd.php', '<?php return "'.md5($password).'"; ?>'); 
		return true; 
	}
	private function _request($matches){ 
		switch($matches[1]){
			case "CREATE TABLE":
				preg_match_all('/'.$this->_regexp['createTableData'].'/ms', $matches[3], $matchesData, PREG_SET_ORDER, 0);
				return $this->__createTable($matches[2], $matchesData);
				break;
			case "ADD DATA TO":
				$this->___checkTable($matches[2]);
				if(core::$error[0] > -1) return false;
				preg_match_all('/'.$this->_regexp['addData'].'/s', $matches[3].',', $matchesDataColumn, PREG_SET_ORDER, 0);
				preg_match_all('/'.$this->_regexp['addData'].'/s', $matches[4].',', $matchesData, PREG_SET_ORDER, 0);
				return $this->__addData($matches[2], $matchesDataColumn, $matchesData);
				var_dump($matches);
				break;
			case "SELECT":
				$this->___checkTable($matches[3]);
				if(core::$error[0] > -1) return false;
				return $this->__selectData($matches[3], isset($matches[5])?$matches[5]:null);
				break;
			case "UPDATE":
				$this->___checkTable($matches[2]);
				if(core::$error[0] > -1) return false;
				preg_match_all('/'.$this->_regexp['getData'].'/ms', $matches[3].',', $matchesSet, PREG_SET_ORDER, 0);
				return $this->__updateData($matches[2], $this->___getArrayData($matchesSet, 2), isset($matches[4])?$matches[4]:null);
				break;
			case "DELETE":
				$this->___checkTable($matches[2]);
				if(core::$error[0] > -1) return false;
				return $this->__deleteData($matches[2], isset($matches[4])?$matches[4]:null);
				break;
			case "ADVENCED":
				switch($matches[2]){
					case 'GET':
						switch($matches[3]){
							case 'tableList':
								return $this->__tableList();
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
	private function __tableList(){
		core::setError();
		$scandir = glob($this->connection[$this->activeConnect]['path'].'*.{fdb}', GLOB_BRACE);
		foreach($scandir as $id => $name){
			$explode = explode('/', $name);
			$scandir[$id] = str_replace('.fdb', '', $explode[count($explode)-1]);
		}
		return $scandir;
	}
	private function __createTable(string $name, array $data){ 
		core::setError(); 
		$name = htmlspecialchars(basename($name)); 
		if(file_exists($this->connection[$this->activeConnect]['path'].$name.'.fdb')) 
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
		return $this->____saveFile($name, $table); 
	}
	private function __addData(string $tableName, array $column, array $data){ 
		$tableName = htmlspecialchars(basename($tableName)); 
		$column = $this->___getArrayData($column); 
		$data = $this->___getArrayData($data); 
		if(count($column) <> count($data)) 
			return core::setError(50, 'data count error'); 
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
		$data = $this->____readFile(htmlspecialchars(basename($tableName)))['data'];
		if($where <> null){ 
			$data = $this->__search($data, $where); 
			if(core::$error[0] > -1)
				return false;
			$data = array_values(array_filter($data));
		}
		return $data; 
	}
	private function __deleteData(string $tableName, string $where=null){
		$tableName = htmlspecialchars(basename($tableName)); 
		$readFile = $this->____readFile($tableName); 
		$data = $readFile['data']; 
		if($where <> null){ 
			$data = $this->__search($data, $where); 
			if(core::$error[0] > -1)
				return false;
		}
		$keys = array_keys($data);
		if(count($keys) == 0)
			return false;
		foreach($keys as $id)
			unset($readFile['data'][$id]);
		$readFile['data'] = array_values(array_filter($readFile['data']));
		$this->____saveFile($tableName, $readFile); 
		return true;
	}
	private function __updateData(string $tableName, array $set, string $where = null){
		$tableName = htmlspecialchars(basename($tableName)); 
		$readFile = $this->____readFile($tableName); 
		$data = $readFile['data']; 
		if($where <> null) 
			$data = $this->__search($data, $where);
			if(core::$error[0] > -1)
				return false;
		foreach(array_keys($data) as $key)
			foreach($set as $columnName => $columnText){
				if(!isset($readFile['data'][$key][$columnName]))
					return core::setError(21, 'column not found', 'name: '.$columnName);
				$readFile['data'][$key][$columnName] = $columnText;
			}
		$this->____saveFile($tableName, $readFile); 
		return true;
	}
	private function __search(array $data, string $search){ 
		foreach(explode(' and ', $search) as $item){
			preg_match_all('/'.$this->_regexp['search'].'/ms', $item, $find, PREG_SET_ORDER, 0);
			$find = $find[0];
			switch($find[2]){
				case '=':
					foreach($data as $dataID => $dataData){
						if(!isset($dataData[$find[1]]))
							return core::setError(21, 'column not found', 'name: '.$find[1]);
						if($dataData[$find[1]] <> $find[3])
							unset($data[$dataID]);
					}
					break;
				case '%':
					foreach($data as $dataID => $dataData){
						if(!isset($dataData[$find[1]]))
							return core::setError(21, 'column not found', 'name: '.$find[1]);
						if(core::$library->string->strpos($dataData[$find[1]], $find[3]) == -1)
							unset($data[$dataID]);
					}
					break;
			}
		}
		return $data;
	}
	private function ___checkTable(string $tableName){
		if(!file_exists($this->connection[$this->activeConnect]['path'].$tableName.'.fdb'))
			return core::setError(20, 'table is not already exists');
		return true;
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
	private function ____saveFile(string $tableName, array $data){ 
		$password = $this->connection[$this->activeConnect]['pass'];
		$data['column'] = core::$library->crypt->crypt(json_encode($data['column']), $password);
		$data['data'] = core::$library->crypt->crypt(json_encode($data['data']), $password);
		$data['option']['autoincrement'] = core::$library->crypt->crypt(json_encode($data['option']['autoincrement']), $password);
		$data['option'] = json_encode($data['option']); 
		$writeFile = $data['option'].PHP_EOL 
					.$data['column'].PHP_EOL 
					.$data['data']; 
		file_put_contents($this->connection[$this->activeConnect]['path'].$tableName.'.fdb', $writeFile);
	}
	private function ____readFile(string $tableName, string $type = 'all'){
		$password = $this->connection[$this->activeConnect]['pass'];
		$readFile = file($this->connection[$this->activeConnect]['path'].$tableName.'.fdb');
		switch($type){
			case "option":
				return json_decode($readFile[0], true);
				break;
			case "column":
				return json_decode(core::$library->crypt->decrypt($readFile[1], $password), true);
				break;
			case "data":
				return json_decode(core::$library->crypt->decrypt($readFile[2], $password), true);
				break;
		}
		$readFile = [
			'option' => json_decode($readFile[0], true), 
			'column' => json_decode(core::$library->crypt->decrypt($readFile[1], $password), true),
			'data' => json_decode(core::$library->crypt->decrypt($readFile[2], $password), true)
		];
		$readFile['option']['autoincrement'] = json_decode(core::$library->crypt->decrypt($readFile['option']['autoincrement'], $password), true);
		return $readFile; 
	}
}
?>