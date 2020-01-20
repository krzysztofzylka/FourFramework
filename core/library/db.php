<?php
return $this->db = new class(){
	public $version = '1.1.1';
	public $tableVersion = '1.1';
	public $lastInsertID = null;
	private $path = '';
	private $connection = [];
	private $acceptType = ['string', 'int', 'integer', 'bool', 'boolean', 'text'];
	private $activeConnect = null;
	private $regex = [
		'(SELECT) ?(.+?) ?FROM (.*) [WHERE]+ ?(.*)?', //select where
		'(SELECT) ?(.+?) ?FROM (.*) ?', //select
		'(ADD DATA TO) (.+) \((.+)\) VALUES \((.+)\)', //add data to
		'(CREATE TABLE) (.*?) {(.*?)}', //create table
		'(UPDATE) (.+) SET (.+) [WHERE]+ (.*)?', //update where
		'(UPDATE) (.+) SET (.+)', //update
		'(DELETE) FROM (.+) [WHERE]+ (.*)?', //delete where
		'(DELETE) FROM (.+)', //delete
		'(ADVENCED) (GET) (.+) FROM (.+)', //ADVENCED GET FROM
		'(ADVENCED) (GET) (.+)', //ADVENCED GET
		'(ADVENCED) (SET) (.+) FROM (.+)', //ADVENCED SET FROM
		'(ADVENCED) (SET) (.+)', //ADVENCED SET
		'(ALTER TABLE) (.+) (ADD) (.+)' //ALTER TABLE ADD COLUMN
	];
	private $advencedLog = true;
	public function __construct(){
		core::setError();
		$this->path = core::$path['base'].'db\\';
		if(!file_exists($this->path))
			mkdir($this->path, 0700, true);
	}
	public function connect(string $name, string $password = null){
		core::setError();
		$name = basename(htmlspecialchars($name));
		$path = $this->path.$name.'\\';
		if(!file_exists($path))
			return core::setError(1, 'database is not exists');
		$pathPass = $path.'passwd.php';
		if(!file_exists($pathPass))
			return core::setError(2, 'pass file is not exists');
		$dbPass = include($pathPass);
		if($dbPass <> md5($password))
			return core::setError(3, 'password incorect');
		$uniqueID = core::$library->string->generateString(10, [true, true, false, false]);
		$this->connection[$uniqueID] = [
			'name' => $name,
			'path' => $path,
			'pass' => md5($password)
		];
		return $uniqueID;
	}
	public function request(string $script, string $connect = null){
		core::setError();
		if((count($this->connection) == 0) and (!isset($this->connection[$connect]) or !is_array($this->connection[$connect])))
			return core::setError(1, 'connection error'); //error 1
		$connect = $connect??array_keys($this->connection)[0];
		foreach($this->regex as $regexp){
			preg_match_all('/'.$regexp.'/msi', $script, $matches, PREG_SET_ORDER, 0);
			if(count($matches) > 0){
				$this->activeConnect = $connect;
				unset($matches[0][0]);
				$matches[0] = array_values($matches[0]);
				$request = $this->_request($matches[0]);
				$this->activeConnect = null; 
				return $request;
			}
		}
		return core::setError(2, 'the script is invalid');
	}
	public function createDatabase(string $name, string $password = null){
		core::setError();
		$path = $this->path.basename(htmlspecialchars($name)).'\\';
		if(file_exists($path))
			return core::setError(1, 'database is already exists');
		mkdir($path, 0700, true);
		file_put_contents($path.'passwd.php', '<?php return "'.md5($password).'"; ?>');
		return true;
	}
	private function _request(array $matches){
		core::setError();
		$this->___log(['$matches' => $matches]);
		switch($matches[0]){
			case 'SELECT':
				$matches[1] = trim($matches[1]);
				if($matches[1] === '')
					$matches[1] = '*';
				return $this->__selectData($matches[2], isset($matches[3])?$matches[3]:null, $matches[1]);
				break;
			case 'ADD DATA TO':
				$matches[1] = core::$library->string->removeQuotes($matches[1]);
				return $this->__addData($matches[1], $matches[2], $matches[3]);
				break;
			case 'CREATE TABLE':
				$matches[1] = core::$library->string->removeQuotes($matches[1]);
				return $this->__createTable($matches[1], $matches[2]);
				break;
			case 'UPDATE':
				$matches[1] = core::$library->string->removeQuotes($matches[1]);
				$matches[2] = core::$library->string->explode(',', $matches[2]);
				for($i=0; $i<count($matches[2]); $i++){
					$matches[2][$i] = core::$library->string->explode('=', $matches[2][$i]);
					$matches[2][$i][0] = core::$library->string->removeQuotes($matches[2][$i][0]);
					$matches[2][$i][1] = core::$library->string->removeQuotes($matches[2][$i][1]);
				}
				return $this->__updateData($matches[1], $matches[2], isset($matches[3])?$matches[3]:null);
				break;
			case 'DELETE':
				$matches[1] = core::$library->string->removeQuotes($matches[1]);
				return $this->__deleteData($matches[1], isset($matches[2])?$matches[2]:null);
				break;
			case 'ADVENCED':
				$matches[2] = core::$library->string->removeQuotes($matches[2]);
				return $this->__advenced($matches[2], $matches[1], isset($matches[3])?core::$library->string->removeQuotes($matches[3]):null);
				break;
			case 'ALTER TABLE':
				$matches[1] = core::$library->string->removeQuotes($matches[1]);
				return $this->__alterTable($matches[1], $matches[2], $matches[3]);
				break;
			default:
				return core::setError(2, 'the script is invalid');
				break;
		}
	}
	private function __alterTable(string $tableName, string $type, string $code){
		core::setError();
		$this->___log(['$tableName' => $tableName, '$type' => $type, '$code' => $code]);
		if($this->___checkTable($tableName) === false)
			return false;
		$readFile = $this->____readFile($tableName);
		preg_match_all('/[\'|\"|\`]([a-zA-Z0-9]+)[\'|\"|\`] (([a-zA-Z0-9]+)(\([0-9]+\))|text|bool)[ |,]?(autoincrement)?/msi', $code, $code, PREG_SET_ORDER, 0);
		if(array_search($code[0][3], $this->acceptType) === false)
			return core::setError(102, 'error add column', 'error column type');
		if(core::$library->array->searchByKey($readFile['column'], 'name', $code[0][1]) > -1)
			return core::setError(103, 'error add column', 'column is already exists');
		$column = ['name' => $code[0][1], 'type' => $code[0][3], 'length' => (int)str_replace(['(', ')'], ['', ''], $code[0][4])];
		array_push($readFile['column'], $column);
		for($i=0; $i<count($readFile['data']); $i++){
			switch($code[0][3]){
				case 'bool':
				case 'boolean':
					$column['length'] = 1;
					$data = (int)0;
					break;
				case 'int':
				case 'integer':
					$data = (int)0;
					break;
				case 'text':
					$column['length'] = 0;
					$data = (string)'';
					break;
				case 'string':
				default:
					$data = (string)'';
					break;
			}
			$readFile['data'][$i][$column['name']] = $data;
		}
		$this->____saveFile($tableName, $readFile);
		return true;
	}
	private function __advenced(string $opt, string $type, string $tableName = null){
		core::setError();
		$this->___log(['$opt' => $opt, '$tableName' => $tableName, '$type' => $type]);
		switch($type){
			case 'GET':
				switch($opt){
					case 'tableList':
						$scandir = glob($this->connection[$this->activeConnect]['path'].'*.{fdb}', GLOB_BRACE);
						foreach($scandir as $id => $path)
							$scandir[$id] = str_replace('.fdb', '', basename($path));
						return $scandir;
						break;
					case 'column':
						if($this->___checkTable($tableName) === false)
							return false;
						$read = $this->____readFile($tableName);
						return $read['column'];
						break;
					case 'autoincrement':
						if($this->___checkTable($tableName) === false)
							return false;
						$read = $this->____readFile($tableName);
						return $read['option']['autoincrement'];
						break;
					default:
						return core::setError(2, 'the script is invalid');
						break;
				}
			default:
				return core::setError(2, 'the script is invalid');
				break;
		}
	}
	private function __updateData(string $tableName, array $setData, string $where=null){
		core::setError();
		if($this->___checkTable($tableName) === false)
			return false;
		$this->___log(['$tableName' => $tableName, '$setData' => $setData, '$where' => $where]);
		$readFile = $this->____readFile($tableName);
		$data = $readFile['data'];
		if($where <> null)
			$data = $this->__search($data, $where);
		foreach(array_keys($data) as $key)
			foreach($setData as $newData){
				$newData = core::$library->array->trim($newData);
				$newData[0] = core::$library->string->removeQuotes($newData[0]);
				$newData[1] = core::$library->string->removeQuotes($newData[1]);
				if(!isset($readFile['data'][$key][$newData[0]]))
					return core::setError(21, 'column not found', 'name: '.$newData[0]);
				$readFile['data'][$key][$newData[0]] = $newData[1];
			}
		$this->____saveFile($tableName, $readFile);
		return true;
	}
	private function __deleteData(string $tableName, string $where=null){
		core::setError();
		$this->___log(['$tableName' => $tableName, $where => $where]);
		if($this->___checkTable($tableName) === false)
			return false;
		$this->___log(['$tableName' => $tableName, '$where' => $where]);
		$readFile = $this->____readFile($tableName);
		$data = $readFile['data'];
		if($where <> null)
			$data = $this->__search($data, $where);
		foreach(array_keys($data) as $key)
			unset($readFile['data'][$key]);
		$readFile['data'] = array_values(array_filter($readFile['data']));
		$this->____saveFile($tableName, $readFile);
		return count($data);
	}
	private function __createTable(string $name, string $data){ 
		core::setError();
		preg_match_all('/[\'|\"|\`]([a-zA-Z0-9]+)[\'|\"|\`] (([a-zA-Z0-9]+)(\([0-9]+\))|text|bool)[ |,]?(autoincrement)?/msi', $data, $data, PREG_SET_ORDER, 0);
		$name = htmlspecialchars(basename($name));
		$this->___log(['$name' => $name, '$data' => $data]);
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
		$this->___log(['$table' => $table]);
		$column = [];
		foreach($data as $item){
			if(count($item) == 3){
				$item[3] = $item[2];
				$item[4] = 0;
			}
			if(array_search($item[3], $this->acceptType) === false)
				return core::setError(102, 'error create table', 'error column type');
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
	private function __addData(string $tableName, string $column, string $data){
		core::setError();
		if($this->___checkTable($tableName) === false)
			return false;
		$column = core::$library->array->trim(core::$library->string->explode(',', $column));
		foreach($column as $id => $string)
			$column[$id] = core::$library->string->removeQuotes($string);
		$data = core::$library->array->trim(core::$library->string->explode(',', $data));
		foreach($data as $id => $string)
			$data[$id] = core::$library->string->removeQuotes($string);
		$this->___log(['$tableName' => $tableName, '$data' => $data, '$column' => $column]);
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
					case 'integer':
					case 'int':
						$item['type'] = 'integer';
						$newData[$item['name']] = (int)$newData[$item['name']];
						break;
					case 'boolean':
					case 'bool':
						$item['type'] = 'boolean'; 
						$newData[$item['name']] = boolval($newData[$item['name']]);
						$item['length'] = 1;
						break;
				}
				if(gettype($newData[$item['name']]) <> $item['type'] and $item['type'] <> 'text')
					return core::setError(52, 'error data type', 'column: '.$item['name'].', type: '.$item['type'].' ('.gettype($newData[$item['name']]).')');
				if(strlen($newData[$item['name']]) > $item['length'] and $item['type'] <> 'text')
					return core::setError(53, 'error data length', 'column: '.$item['name'].', length: '.strlen($newData[$item['name']]).'/'.$item['length']);
			}
		}
		array_push($readFile['data'], $newData);
		if($autoincrement['ai'] == true)
			$this->lastInsertID = $autoincrement['count'];
		else
			$this->lastInsertID = null;
		$this->____saveFile($tableName, $readFile);
		return true;
	}
	private function __selectData(string $tableName, string $where=null, string $wData='*'){
		core::setError();
		if($this->___checkTable($tableName) === false)
			return false;
		$data = $this->____readFile(htmlspecialchars(basename($tableName)))['data'];
		if($where <> null)
			$data = $this->__search($data, $where);
		if($wData <> '*'){
			$find = core::$library->array->trim(core::$library->string->explode(',', $wData));
			foreach($find as $id => $string)
				$find[$id] = core::$library->string->removeQuotes($string);
			$noDelete = [];
			foreach($find as $arr)
				array_push($noDelete, $arr);
			foreach($data as $id => $array)
				foreach($array as $arrayName => $string)
					if(array_search($arrayName, $noDelete) === false)
						unset($data[$id][$arrayName]);
		}
		$data = array_values($data);
		$this->___log(['$tableName' => $tableName, '$where' => $where, '$wData' => $wData, '$data' => $data]);
		return $data;
	}
	private function __search(array $data, string $where){
		core::setError();
		$explode = core::$library->string->explode(' and ', $where);
		$this->___log(['data' => $data, '$where' => $where, '$explode' => $explode]);
		foreach($explode as $item){
			preg_match_all("/(.+) ?([=|&]) ?(.+)/msi", $item, $itemMatches, PREG_SET_ORDER, 0);
			unset($itemMatches[0][0]);
			$find = array_values($itemMatches[0]);
			$find[0] = core::$library->string->removeQuotes($find[0]);
			$find[2] = core::$library->string->removeQuotes($find[2]);
			$this->___log(['$find[0]' => $find[0], '$find[2]' => $find[2]]);
			switch($find[1]){
				case '=':
					foreach($data as $dataID => $dataData){
						if(!isset($dataData[$find[0]]))
							return core::setError(21, 'column not found', 'name: '.$find[0]);
						if($dataData[$find[0]] <> $find[2])
							unset($data[$dataID]);
					}
					break;
				case '%':
					foreach($data as $dataID => $dataData){
						if(!isset($dataData[$find[0]]))
							return core::setError(21, 'column not found', 'name: '.$find[0]);
						if(core::$library->string->strpos($dataData[$find[0]], $find[2]) == -1)
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
	private function ___log($data){
		core::setError();
		if($this->advencedLog === false)
			return false;
		echo '<pre>';
		var_dump($data);
		echo '</pre>';
	}
	private function ____saveFile(string $tableName, array $data){ 
		core::setError();
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
		core::setError();
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