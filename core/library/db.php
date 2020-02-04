<?php
return $this->db = new class(){
	public $version = '1.1.3';
	public $tableVersion = '1.1';
	public $lastInsertID = null;
	private $path = '';
	private $connection = [];
	private $acceptType = ['string', 'integer', 'boolean', 'text'];
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
		'(ALTER TABLE) (.+) (ADD) (.+)', //ALTER TABLE ADD COLUMN
		'(REPAIR TABLE) (.+)' //REPAIR TABLE
	];
	private $advencedLog = true; //false
	private $saveDBFile = false; //true
	public function __construct(){
		core::setError();
		$this->path = core::$path['base'].'db/';
		if(!file_exists($this->path))
			mkdir($this->path, 0700, true);
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
		$dbPass = include($pathPass);
		if($dbPass <> md5($password))
			return core::setError(3, 'password incorect');
		$uniqueID = core::$library->string->generateString(10, [true, true, false, false]);
		$this->connection[$uniqueID] = [
			'name' => $name,
			'path' => $path,
			'pass' => md5($password)
		];
		if($this->activeConnect === null)
			$this->activeConnect = $uniqueID;
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
	public function databaseList(){
		$return = [];
		$path = core::$path['base'].'db/';
		$scan = scandir($path);
		$scan = array_diff($scan, ['.', '..', '.htaccess']);
		foreach($scan as $name){
			$dbPath = $path.$name.'/';
			if(file_exists($dbPath.'passwd.php')){
				$tabele = [];
				$scanTable = scandir($dbPath);
				// $scanTable = array_diff($scanTable, ['.', '..', '.htaccess', 'passwd.php']);
				foreach($scanTable as $id => $tableName){
					if(substr($tableName, strlen($tableName)-3) == 'fdb')
						array_push($tabele, substr($tableName, 0, strlen($tableName)-4));
				}
				$return[$name] = [
					'name' => $name,
					'table' => $tabele
				];
			}
		}
		return $return;
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
			case 'REPAIR TABLE':
				$matches[1] = core::$library->string->removeQuotes($matches[1]);
				return $this->__repairTable($matches[1]);
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
		switch($type){
			case 'ADD': //add column
				$readFile = $this->____readFile($tableName);
				$code = $this->___columnDataExplode($code);
				if(core::$error[0] > -1)
					return core::setError(102, 'error add column', 'error column type');
				$this->___log(['$code' => $code]);
				if(core::$library->array->searchByKey($readFile['column'], 'name', $code['name']) > -1)
					return core::setError(103, 'error add column', 'column is already exists');
				$column = ['name' => $code['name'], 'type' => $code['type'], 'length' => $code['length']];
				$this->___log(['$column' => $column]);
				array_push($readFile['column'], $column);
				for($i=0; $i<count($readFile['data']); $i++)
					$readFile['data'][$i][$column['name']] = $code['data'];
				$readFile['column']['count']++;
				$this->____saveFile($tableName, $readFile);
				break;
			default:
				return core::setError(2, 'the script is invalid');
				break;
		}
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
		$name = htmlspecialchars(basename($name));
		$data = core::$library->string->explode(',', $data);
		$data = core::$library->array->trim($data);
		foreach($data as $id => $item){
			$data[$id] = $this->___columnDataExplode($item);
			if(core::$error[0] > -1)
				return core::setError(102, 'error add column', 'error column type');
		}
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
		$column = [];
		foreach($data as $item){
			array_push($table['column'], [
				'name' => (string)$item['name'],
				'type' => (string)$item['type'],
				'length' => $item['length']
			]);
			if($item['autoincrement'] === true){
				$table['option']['autoincrement']['ai'] = true;
				$table['option']['autoincrement']['id'] = $table['column']['count'];
			}
		}
		$table['column']['count'] = count($table['column'])-1;
		$this->___log(['$table' => $table]);
		return $this->____saveFile($name, $table);
	}
	private function __addData(string $tableName, string $column, string $data){
		core::setError();
		if($this->___checkTable($tableName) === false)
			return false;
		$this->lastInsertID = null;
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
		$autoincrement = $readFile['option']['autoincrement'];
		$arrayData = array_combine($column, $data);
		$tableItem = $this->___checkTableItem($arrayData, $readFile['column'], $autoincrement);
		$this->___log(['___checkTableItem' => $tableItem]);
		$readFile['option']['autoincrement']['count']++;
		array_push($readFile['data'], $tableItem);
		if($autoincrement['ai'] == true)
			$this->lastInsertID = $autoincrement['count'];
		$this->____saveFile($tableName, $readFile);
		return true;
	}
	private function __selectData(string $tableName, string $where=null, string $wData='*'){
		core::setError();
		if($this->___checkTable($tableName) === false)
			return false;
		$data = $this->____readFile(htmlspecialchars(basename($tableName)))['data'];
		$column = $this->____readFile(htmlspecialchars(basename($tableName)))['column'];
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
		$data = $this->___convertDataType($data, $column);
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
	private function __repairTable(string $tableName){
		core::setError();
		if($this->___checkTable($tableName) === false)
			return false;
		$readTable = $this->____readFile(htmlspecialchars(basename($tableName)));
		$readTable['data'] = $this->___convertDataType($readTable['data'], $readTable['column']);
		foreach($readTable['column'] as $id => $item){
			switch($item['type']){
				case 'int':
					$readTable['column'][$id]['type'] = 'integer';
					break;
				case 'bool':
					$readTable['column'][$id]['type'] = 'boolean';
					break;
			}
		}
		$this->___log(['$readTable' => $readTable]);
		$this->____saveFile($tableName, $readTable);
		return true;
	}
	private function ___checkTableItem(array $arrData, array $column, array $ai){
		$this->___log(['$arrData' => $arrData, '$column' => $column, '$ai' => $ai]);
		$return = [];
		foreach($column as $id => $item){
			if($item['name'] === null)
				continue;
			if($ai['ai'] === true and $id === $ai['id']){
				$return[$item['name']] = (int)$ai['count'];
				continue;
			}
			if(!isset($arrData[$item['name']]))
				return core::setError(51, 'error search column', 'error find column \''.$item['name'].'\'');
			switch($item['type']){
				case 'integer':
				case 'int':
					$item['type'] = 'integer';
					$return[$item['name']] = (int)$return[$item['name']];
					break;
				case 'boolean':
				case 'bool':
					$item['type'] = 'boolean'; 
					$return[$item['name']] = boolval($return[$item['name']]);
					$item['length'] = 1;
					break;
				default:
					$return[$item['name']] = $arrData[$item['name']];
					break;
			}
			if(gettype($return[$item['name']]) <> $item['type'] and $item['type'] <> 'text')
				return core::setError(52, 'error data type', 'column: '.$item['name'].', type: '.$item['type'].' ('.gettype($return[$item['name']]).')');
			if(strlen($return[$item['name']]) > $item['length'] and $item['type'] <> 'text')
				return core::setError(53, 'error data length', 'column: '.$item['name'].', length: '.strlen($return[$item['name']]).'/'.$item['length']);
		}
		return $return;
	}
	private function ___checkTable(string $tableName){
		core::setError();
		$this->___log(['activeConnect' => $this->activeConnect]);
		if($this->activeConnect === null)
			return core::setError(3, 'connection not exists');
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
	private function ___columnDataExplode(string $data){
		core::setError();
		$return = ['name' => null, 'type' => null, 'length' => null, 'autoincrement' => null, 'data' => null];
		$explode = core::$library->string->explode(' ', $data);
		$return['name'] = core::$library->string->removeQuotes($explode[0]);
		if(count($explode) >= 2){
			$length = core::$library->string->between($explode[1], '(', ')', 0);
			if($length === null)
				$return['length'] = 0;
			else{
				$return['length'] = (int)$length;
				$explode[1] = str_replace('('.$length.')', '', $explode[1]);
			}
			switch($explode[1]){
				case 'bool':
				case 'boolean':
					$explode[1] = 'boolean';
					$return['length'] = 1;
					$return['data'] = (int)0;
					break;
				case 'int':
				case 'integer':
					$explode[1] = 'integer';
					$return['data'] = (int)0;
					break;
				default:
					$return['data'] = (string)"";
					break;
			}
			$return['type'] = $explode[1];
			$search = array_search($return['type'], $this->acceptType);
			if($search === false)
				return core::setError(1, 'Error column data type', 'type: '.$return['type']);
		}
		if(count($explode) == 3){
			if($explode[2] == 'autoincrement')
				$return['autoincrement'] = true;
			else
				$return['autoincrement'] = false;
		}else
			$return['autoincrement'] = false;
		return $return;
	}
	private function ___convertDataType(array $data, array $column){
		core::setError();
		foreach($column as $item){
			switch($item['type']){
				case 'string':
					break;
				case 'int':
				case 'integer':
					foreach($data as $id => $item2){
						if(!isset($data[$id][$item['name']]))
							return core::setError(21, 'column not found', 'name: '.$item['name']);
						$data[$id][$item['name']] = (int)$item2[$item['name']];
					}
					break;
				case 'bool':
				case 'boolean':
					foreach($data as $id => $item2){
						if(!isset($data[$id][$item['name']]))
							return core::setError(21, 'column not found', 'name: '.$item['name']);
						$data[$id][$item['name']] = boolval($item2[$item['name']]);
					}
					break;
			}
		}
		return $data;
	}
	private function ____saveFile(string $tableName, array $data){ 
		core::setError();
		if($this->saveDBFile === false)
			return false;
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