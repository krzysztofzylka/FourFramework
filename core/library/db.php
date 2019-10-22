<?php
return $this->db = new class(){ //create db library
	public $version = '1.0.3'; //version
	public $tableVersion = '1.1'; //table version
	public $path = ''; //database path
	private $connection = []; //connection list
	private $_regexp = [
		'fileName' => '([a-zA-Z0-9\-\_\!\@\#\$\%\^\&\(\)\=]+)',
		'getData' => '[\'|"|`]?([a-zA-Z0-9]+)[\'|"|`]? ?= ?([\'|"|`]?(.+?)[\'|"|`]?|([a-zA-Z0-9\"\']+))[,|\n]', //'name' = 'tresc', 'name2' = 'tresc2'
		'createTableData' => '[\'|\"|\`](.+?)[\'|\"|\`] ((int|string|bool)(\([0-9]+\))|text)[ |,]?(autoincrement)?', //'name' (int|String|bool|(int) autoincrement?
		'addData' => '[\'|\"|\`]?(.+?)[\'|\"|\`]?, ?', //'data', 'data2'
		'request' => [
			'createTable' => '(CREATE TABLE) [\'|\"|\`]?([a-zA-Z0-9]+)[\'|\"|\`]? {(.+)}',
			'addDataTo' => '(ADD DATA TO) [\'|"|`]?{$fileName}[\'|"|`]? \((.+)\) VALUES \((.+)\)',
			'select' => '(SELECT) [\'|"|`]?(.+?)?[\'|"|`]? ?(FROM) ["|\'|`]?{$fileName}["|\'|`]? ?(WHERE (.+))?',
			'advenced' => '(ADVENCED) (GET|SET) (.+) FROM ["|\'|`]?{$fileName}["|\'|`]? ?(OPTION+ ?(.+))?',
			'updateWhere' => '(UPDATE) ["|\'|`]?{$fileName}["|\'|`]? SET (.+) WHERE (.+)',
			'update' => '(UPDATE) ["|\'|`]?{$fileName}["|\'|`]? SET (.+)'
		]
	];
	private $activeConnect = null; //active connect
	private $debug = true;
	public function __construct(){ //main function
		core::setError(); //clear error
		$this->path = core::$path['base'].'db/'; //create path
		if(!file_exists($this->path))
			mkdir($this->path, 0700, true); //create dir
		foreach($this->_regexp['request'] as $name => $data) //replace regexp
			$this->_regexp['request'][$name] = preg_replace_callback('/{\$([a-zA-Z0-9]+)}/ms', 'self::___replaceRegexp', $this->_regexp['request'][$name]); //replace regexp
	}
	public function request(string $script, string $connect = null){ //request script to database
		core::setError(); //clear error
		$connect = $connect??array_keys($this->connection)[0]; //if first connect
		if($this->___checkConnect($connect) == false) //check connect (is false)
			return core::setError(1, 'connection error'); //return error
		foreach($this->_regexp['request'] as $regexp){ //regexp loop
			preg_match_all('/'.$regexp.'/ms', $script, $matches, PREG_SET_ORDER, 0); //preg match
			if(count($matches) > 0){ //search
				$this->activeConnect = $connect; //define active connect
				$request = $this->_request($matches[0]); //get return request
				$this->activeConnect = null; //define active connect
				return $request; //return request
			}
		}
	}
	public function connect(string $name, string $password = null){ //connect to database
		$name = basename(htmlspecialchars($name)); //protect db name
		$path = $this->path.$name.'/'; //create path
		if(!file_exists($path)) //if db not exists
			return core::setError(1, 'database is not exists'); //return error
		$pathPass = $path.'passwd.php'; //test pass file path
		if(!file_exists($pathPass)) //if db not exists
			return core::setError(2, 'pass file is not exists'); //return error
		$passMD5 = include($pathPass); //read password
		if($passMD5 <> md5($password)) //check password
			return core::setError(3, 'password incorect'); //return error
		$connArray = [
			'name' => $name, //name
			'path' => $path, //path
			'pass' => md5($password) //password
		]; //create connection array
		$uniqueID = core::$library->string->generateString(10, [true, true, false, false]); //generate connection uniqueID
		$this->connection[$uniqueID] = $connArray; //add connection to array
		return $uniqueID; //return uniqueID
	}
	public function createDatabase(string $name, string $password = null){ //create database
		$name = basename(htmlspecialchars($name)); //protect db name
		$path = $this->path.$name.'/'; //create path
		if(file_exists($path)) //if db exists
			return core::setError(1, 'database is already exists'); //return error
		mkdir($path, 0700, true); //create database
		$pathPass = $path.'passwd.php'; //test pass file path
		file_put_contents($pathPass, '<?php return "'.md5($password).'"; ?>'); //create pass file
		return true; //return success
	}
	private function _request($matches){ //request
		$this->___debug($matches, '_request'); //debug
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
				// return $this->__advenced($matches
				break;
		}
	}
	private function __createTable(string $name, array $data){ //create table
		core::setError(); //clear error
		$name = htmlspecialchars(basename($name)); //protect table name
		$this->___debug($name, 'name'); //debug
		$DBpath = $this->connection[$this->activeConnect]['path'].$name.'.fdb'; //generate table file
		$this->___debug($DBpath, 'DBpath'); //debug
		if(file_exists($DBpath)) //if table exists
			return core::setError(101, 'error create table', 'table is already exists'); //return error
		$table = [ //generate table array
			'option' => [ //option
				'name' => $name, //name
				'version' => $this->tableVersion, //table version
				'autoincrement' => [ //ai
					'ai' => false, //ai bool
					'id' => -1, //ai id
					'count' => 1 //ai count
				]
			],
			'column' => [
				'count' => 0 //column count
			],
			'data' => [] //data
		];
		$column = []; //empty column array
		foreach($data as $item){ //foreach matches data
			if(count($item) < 5) //array count
				return core::setError(100, 'error create table', 'error generate column'); //return error
			$column = [
				'name' => (string)$item[1], //column name
				'type' => (string)$item[3], //data type
				'length' => (int)str_replace(['(', ')'], ['', ''], $item[4]) //max data length
			];
			if(isset($item[5]) and $item[5] == 'autoincrement'){ //check autoincrement
				$table['option']['autoincrement']['ai'] = true; //set bool
				$table['option']['autoincrement']['id'] = $table['column']['count']; //set column id
			}
			$table['column'][$table['column']['count']] = $column; //add column to table
			$table['column']['count']++; //add column count +1
		}
		$this->___debug($table, 'table'); //debug table
		return $this->____saveFile($name, $table); //save to file
	}
	private function __addData(string $tableName, array $column, array $data){ //add data
		$tableName = htmlspecialchars(basename($tableName)); //protect table name
		$column = $this->___getArrayData($column); //replace column
		$data = $this->___getArrayData($data); //replace data
		if(count($column) <> count($data)) //check column and data count
			return core::setError(50, 'data count error'); //return error
		$this->___debug($tableName, 'tableName'); //debug
		$this->___debug($column, 'column'); //debug
		$this->___debug($data, 'data'); //debug
		$readFile = $this->____readFile($tableName); //read table
		$newData = []; //new data array
		$autoincrement = $readFile['option']['autoincrement']; //ai table
		foreach($readFile['column'] as $id => $item){ //column loop
			if(is_array($item)){ //if array
				$search = array_search($item['name'], $column); //search in column
				if(is_int($search)) //if search
					$newData[$column[$search]] = $data[$search]; //add data
				if($autoincrement['ai'] == true and $item['name'] == $readFile['column'][$autoincrement['id']]['name']){ //check if autoincrement
					$newData[$item['name']] = $autoincrement['count']; //get counter
					$readFile['option']['autoincrement']['count']++; //add 1 to counter
					$search = -1; //search ai
				}
				if(is_bool($search)) //if not exists column
					core::setError(51, 'error search column', 'error find column \''.$item['name'].'\''); //return error
				$columnName = $search==-1?$readFile['column'][$autoincrement['id']]['name']:$column[$search];
				switch($item['type']){ //switch item type
					case 'int': //if int
						$item['type'] = 'integer'; //set integer
						break;
				}
				if(gettype($newData[$item['name']]) <> $item['type']) //data type error
					return core::setError(52, 'error data type', 'column: '.$item['name'].', type: '.$columnType.' ('.gettype($dane).')'); //return error
				if(strlen($newData[$item['name']]) > $item['length'])
					return core::setError(53, 'error data length', 'column: '.$item['name'].', length: '.strlen($newData[$item['name']]).'/'.$item['length']); //return error
				// var_dump([
					// 'search' => $search,
					// 'item name' => $item['name'],
					// 'column' => $column,
					// 'data' => $data,
					// 'column name' => $columnName,
					// 'data type' => gettype($newData[$item['name']]),
					// 'column type' => $item['type'],
					// 'data length' => strlen($newData[$item['name']]),
					// 'column length' => $item['length'],
				// ]);
			}
		}
		array_push($readFile['data'], $newData); //add data to array
		$this->____saveFile($tableName, $readFile); //save data to file
		return true;
	}
	private function __selectData(string $tableName, string $where=null){
		$tableName = htmlspecialchars(basename($tableName)); //protect table name
		$readFile = $this->____readFile($tableName); //read table
		$data = $readFile['data']; //get data
		if($where <> null){ //if where
			$data = $this->__search($data, $where); //search
			$data = array_values(array_filter($data)); //reindex and clear
		}
		return $data; //return data
	}
	private function __update(string $tableName, array $set, string $where = null){
		$tableName = htmlspecialchars(basename($tableName)); //protect table name
		$readFile = $this->____readFile($tableName); //read table
		$data = $readFile['data']; //get data
		if($where <> null) //if where
			$data = $this->__search($data, $where); //search
		$data = array_keys($data);
		foreach($data as $key)
			foreach($set as $columnName => $columnText)
				$readFile['data'][$key][$columnName] = $columnText;
		$this->____saveFile($tableName, $readFile); //save file
		return true;
	}
	private function __search(array $data, string $search) : array{ //search data
		$explode = explode(' and ', $search); //explode search
		foreach($explode as $item){ //search loop
			//=
			$exp = $this->___searchExplode($item, '='); //search explode item
			if($exp <> false){ //return <> false
				foreach($data as $dataID => $dataData){ //foreach data
					if($dataData[$exp[0]] <> $exp[1]) //search
						unset($data[$dataID]); //delete item
				}
			}
			//%
			$exp = $this->___searchExplode($item, '%'); //search explode item
			if($exp <> false){ //return <> false
				foreach($data as $dataID => $dataData){ //foreach data
					if(core::$library->string->strpos($dataData[$exp[0]], $exp[1]) == -1) //not find
						unset($data[$dataID]); //delete item
				}
			}
		}
		return $data;
	}
	private function __advenced(string $tableName, array $data){
		
	}
	private function ___replaceRegexp($matches){ //replace regexp
		return $this->_regexp[$matches[1]];
	}
	private function ___checkConnect($uniqueID){ //check connect
		if(!isset($this->connection[$uniqueID])) //if not exists
			return false;
		if(!is_array($this->connection[$uniqueID])) //if not array
			return false;
		return true;
	}
	private function ___debug($data, $title=''){ //debug
		if($this->debug)
			core::$library->debug->consoleLog($data, $title); //write to console
	}
	private function ___getArrayData($array, $type=1){ //replace and return 2-nd array data
		switch($type){
			case 1: //only first item
				foreach($array as $id=>$data)
					$array[$id] = $data[1];
				break;
			case 2: //set data
				foreach($array as $id=>$data){
					$array[$data[1]] = $data[3];
					unset($array[$id]);
				}
				break;
		}
		return $array;
	}
	private function ___searchExplode($string, $exp){ //explode function for search
		$explode = explode($exp, $string, 2); //explode string
		if($explode[0] == $string)
			return false; //if error
		return $explode; //return array
	}
	private function ____saveFile(string $tableName, array $data){ //save table to file
		$tablePath = $this->connection[$this->activeConnect]['path'].$tableName.'.fdb'; //generate table file
		$data['column'] = $this->____crypt($data['column']); //crypt column
		$data['data'] = $this->____crypt($data['data']); //crypt data
		$data['option']['autoincrement'] = $this->____crypt($data['option']['autoincrement']); //crypt autoincrement
		$data['option'] = json_encode($data['option']); //encode option
		$writeFile = $data['option'].PHP_EOL //option
					.$data['column'].PHP_EOL //column
					.$data['data']; //data
		file_put_contents($tablePath, $writeFile); //write to file
	}
	private function ____readFile(string $tableName){ //read table from file
		$tablePath = $this->connection[$this->activeConnect]['path'].$tableName.'.fdb'; //generate table file
		$readFile = file($tablePath); //read file to array
		$readFile = [
			'option' => json_decode($readFile[0], true), //read option
			'column' => $this->____decrypt($readFile[1]), //decode column
			'data' => $this->____decrypt($readFile[2]), //decode data
		];
		$readFile['option']['autoincrement'] = $this->____decrypt($readFile['option']['autoincrement']); //decode autoincrement
		return $readFile; //return array
	}
	private function ____crypt($data){ //crypt data
		$password = $this->connection[$this->activeConnect]['pass']; //get password
		if(is_array($data)) //if array
			$data = json_encode($data); //array encode
		return core::$library->crypt->crypt($data, $password); //crypt
	}
	private function ____decrypt(string $data){ //crypt data
		$password = $this->connection[$this->activeConnect]['pass']; //get password
		$data = core::$library->crypt->decrypt($data, $password); //crypt
		return json_decode($data, true); //array encode
	}
}
?>