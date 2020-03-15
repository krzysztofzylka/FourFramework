<?php
return $this->db = new class(){
	public $version = '1.1.7dev';
	public $tableVersion = '1.1'; //wersja bazy danych pliku fdb
	public $lastInsertID = null; //ID ostatnio dodanego pliku (jeżeli tabela ma kolumne autoincrement)
	public $path = '';																			//private
	public $connection = [];																	//private
	public $acceptType = ['string', 'integer', 'boolean', 'text'];								//private
	public $activeConnect = null;																//private
	public $regex = [																			//private
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
	public $advencedLog = false; //false (zaawansowane logi (wyświetlenie print_r))				//private
	public $saveDBFile = true; //true (Zapis danych do pliku po modyfikacji - dla testów)		//private
	public function __construct(){
		core::setError();
		$this->path = core::$path['base'].'db/';
		if(!file_exists($this->path))
			mkdir($this->path, 0700, true);
	}
	//funkcje główne
	public function connect(string $name, string $password = null){
		core::setError();
		$name = basename(htmlspecialchars($name)); //ochrona nazwy
		$password = htmlspecialchars($password); //ochrona hasła
		//generowanie ścieżki do bazy oraz sprawdzenie czy baza istnieje
		$path = $this->path.$name.'/';
		if(!file_exists($path)) return core::setError(1, 'database is not exists');
		//generowanie ścieżki do pliku (passwd.php), sprawdzenie poprawności, import oraz sprawdzenie czy hasło się zgadza
		$pathPass = $path.'passwd.php';
		if(!file_exists($pathPass)) return core::setError(2, 'pass file is not exists');
		$dbPass = include($pathPass);
		if($dbPass <> md5($password)) return core::setError(3, 'password incorect');
		//generowanie unikalnego ID połączenia oraz zapis informacji do tablicy
		while(true){
			$uniqueID = core::$library->string->generateString(10, [true, true, false, false]);
			if(!isset($this->connection[$uniqueID])) break; //jeżeli unikatowy to zamykanie pętli
		}
		$this->connection[$uniqueID] = [
			'name' => $name,
			'path' => $path,
			'pass' => md5($password)
		];
		//zmiana aktywnego połączenia na aktualny unikatowy identyfikator
		if($this->activeConnect === null)
			$this->activeConnect = $uniqueID;
		return $uniqueID; //zwracanie unikalnego ID
	}
	public function request(string $script, string $connect = null){
		core::setError();
		//jeżeli nie połączono z żadną bazą danych
		if((count($this->connection) == 0) and (!isset($this->connection[$connect]) or !is_array($this->connection[$connect])))
			return core::setError(1, 'connection error'); //error 1
		$connect = $connect??array_keys($this->connection)[0]; //pobieranie połączenia (jeżeli nie wybrano to wybranie pierwszego połączenia)
		foreach($this->regex as $regexp){ //pętla regexp
			preg_match_all('/'.$regexp.'/msi', $script, $matches, PREG_SET_ORDER, 0);
			if(count($matches) > 0){ //jeżeli znaleziono
				$this->activeConnect = $connect; //ustalenie aktywnego połączenia dla wywołania funkcji
				unset($matches[0][0]); //czyszczenie pierwszego elementu regexp (pełny skrypt)
				$matches[0] = array_values($matches[0]); //pobranie wartości z tablicy
				$request = $this->_request($matches[0]); //wywołanie funkcji _request
				$this->activeConnect = null; //czyszczenie aktywnego połączenia
				return $request;
			}
		}
		return core::setError(2, 'the script is invalid');
	}
	public function createDatabase(string $name, string $password = null){
		core::setError();
		$password = htmlspecialchars($password); //ochrona hasła
		$path = $this->path.basename(htmlspecialchars($name)).'/';
		if(file_exists($path))
			return core::setError(1, 'database is already exists');
		mkdir($path, 0700, true);
		file_put_contents($path.'passwd.php', '<?php return "'.md5($password).'"; ?>');
		return true;
	}
	public function databaseList(){
		core::setError();
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
	public function _request(array $matches){													//private
		core::setError();
		$this->___log(['$matches' => $matches], '_request');
		switch($matches[0]){
			case 'SELECT':
				$matches = core::$library->array->trim($matches);
				if($matches[1] === '') $matches[1] = '*';
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
	//skrypty zarządzające bazą
	public function __alterTable(string $tableName, string $type, string $code){				//private
		core::setError();
		$this->___log(['$tableName' => $tableName, '$type' => $type, '$code' => $code], '__alterTable');
		if($this->___checkTable($tableName) === false)
			return false;
		switch($type){
			case 'ADD': //add column
				$readFile = $this->____readFile($tableName);
				$code = $this->___columnDataExplode($code);
				if(core::$error[0] > -1)
					return core::setError(102, 'error add column', 'error column type');
				$this->___log(['$code' => $code], '__alterTable');
				if(core::$library->array->searchByKey($readFile['column'], 'name', $code['name']) > -1)
					return core::setError(103, 'error add column', 'column is already exists');
				$column = ['name' => $code['name'], 'type' => $code['type'], 'length' => $code['length']];
				$this->___log(['$column' => $column], '__alterTable');
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
	public function __advenced(string $opt, string $type, string $tableName = null){			//private
		core::setError();
		$this->___log(['$opt' => $opt, '$tableName' => $tableName, '$type' => $type], '__advenced');
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
	public function __updateData(string $tableName, array $setData, string $where=null){		//private
		core::setError();
		if($this->___checkTable($tableName) === false)
			return false;
		$this->___log(['$tableName' => $tableName, '$setData' => $setData, '$where' => $where], '__updateData');
		$readFile = $this->____readFile($tableName);
		$data = $readFile['data'];
		if($where <> null){
			$data = $this->__search($data, $where);
			if(core::$error[0] > -1) return false; //check error
		}
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
	public function __deleteData(string $tableName, string $where=null){						//private
		core::setError();
		$this->___log(['$tableName' => $tableName, $where => $where], '__deleteData');
		if($this->___checkTable($tableName) === false)
			return false;
		$this->___log(['$tableName' => $tableName, '$where' => $where], '__deleteData');
		$readFile = $this->____readFile($tableName);
		$data = $readFile['data'];
		if($where <> null){
			$data = $this->__search($data, $where);
			if(core::$error[0] > -1) return false; //check error
		}
		foreach(array_keys($data) as $key)
			unset($readFile['data'][$key]);
		$readFile['data'] = array_values(array_filter($readFile['data']));
		$this->____saveFile($tableName, $readFile);
		return count($data);
	}
	public function __createTable(string $name, string $data){ 									//private
		core::setError();
		$name = htmlspecialchars(basename($name));
		$data = core::$library->string->explode(',', $data);
		$data = core::$library->array->trim($data);
		foreach($data as $id => $item){
			$data[$id] = $this->___columnDataExplode($item);
			if(core::$error[0] > -1)
				return core::setError(102, 'error add column', 'error column type');
		}
		$this->___log(['$name' => $name, '$data' => $data], '__createTable');
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
		$this->___log(['$table' => $table], '__createTable');
		return $this->____saveFile($name, $table);
	}
	public function __addData(string $tableName, string $column, string $data){					//private
		core::setError();
		if($this->___checkTable($tableName) === false)
			return false;
		$this->lastInsertID = null;
		//column
		$column = core::$library->array->trim(core::$library->string->explode(',', $column));
		foreach($column as $id => $string)
			$column[$id] = core::$library->string->removeQuotes($string);
		//data
		$data = core::$library->array->trim(core::$library->string->explode(',', $data));
		foreach($data as $id => $string)
			$data[$id] = core::$library->string->removeQuotes($string);
		$this->___log(['$tableName' => $tableName, '$data' => $data, '$column' => $column], '__addData');
		if(count($column) <> count($data))
			return core::setError(50, 'data count error');
		$readFile = $this->____readFile($tableName);
		$autoincrement = $readFile['option']['autoincrement'];
		$arrayData = array_combine($column, $data);
		$tableItem = $this->___checkTableItem($arrayData, $readFile['column'], $autoincrement);
		if(core::$error[0] > -1) return false; //check error
		$this->___log(['___checkTableItem' => $tableItem], '__addData');
		$readFile['option']['autoincrement']['count']++;
		array_push($readFile['data'], $tableItem);
		if($autoincrement['ai'] == true)
			$this->lastInsertID = $autoincrement['count'];
		$this->____saveFile($tableName, $readFile);
		return true;
	}
	public function __selectData(string $tableName, string $where=null, string $wData='*'){		//private
		core::setError();
		if($this->___checkTable($tableName) === false)
			return false;
		$data = $this->____readFile(htmlspecialchars(basename($tableName)), 'data'); //read data
		$column = $this->____readFile(htmlspecialchars(basename($tableName)), 'column'); //read column
		//jeżeli where
		if($where <> null){
			$data = $this->__search($data, $where);
			if(core::$error[0] > -1) return false; //check error
		}
		//zwracanie tylko poszczególnych kolumn
		if($wData <> '*')
			foreach(array_keys($data) as $key)
				foreach(array_keys($data[$key]) as $dataKey)
					if(is_bool(array_search($dataKey, core::$library->array->trim(core::$library->string->explode(',', $wData)))))
						unset($data[$key][$dataKey]);
		$data = array_values($data);
		$data = $this->___convertDataType($data, $column);
		if(core::$error[0] > -1) return false; //check error
		$this->___log(['$tableName' => $tableName, '$where' => $where, '$wData' => $wData, '$data' => $data], '__selectData');
		return $data;
	}
	//funkcje dodatkowe
	public function __search(array $data, string $where){ //wyszukiwarka danych					//private
		core::setError();
		$explode = core::$library->string->explode(' and ', $where);
		$this->___log(['data' => $data, '$where' => $where, '$explode' => $explode], '__search');
		foreach($explode as $item){
			preg_match_all("/(.+) ?([=|&|>|<]) ?(.+)/msi", $item, $itemMatches, PREG_SET_ORDER, 0);
			unset($itemMatches[0][0]);
			$find = array_values($itemMatches[0]);
			$find = core::$library->array->trim($find); //czyszenie danych w tablicy
			$find[0] = core::$library->string->removeQuotes($find[0]);
			$find[2] = core::$library->string->removeQuotes($find[2]);
			$this->___log(['$find' => $find], '__search');
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
				case '>':
					foreach($data as $dataID => $dataData){
						if(!isset($dataData[$find[0]]))
							return core::setError(21, 'column not found', 'name: '.$find[0]);
						if($dataData[$find[0]] < $find[2])
							unset($data[$dataID]);
					}
					break;
				case '<':
					foreach($data as $dataID => $dataData){
						if(!isset($dataData[$find[0]]))
							return core::setError(21, 'column not found', 'name: '.$find[0]);
						if($dataData[$find[0]] > $find[2])
							unset($data[$dataID]);
					}
					break;
			}
		}
		$this->___log(['$data' => $data], '__search');
		return $data;
	}
	public function __repairTable(string $tableName){ //funkcja naprawy tabeli					//private
		core::setError();
		if($this->___checkTable($tableName) === false)
			return false;
		$readTable = $this->____readFile(htmlspecialchars(basename($tableName)));
		$readTable['data'] = $this->___convertDataType($readTable['data'], $readTable['column']);
		if(core::$error[0] > -1) return false; //check error
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
	public function ___checkTableItem(array $arrData, array $column, array $ai){ //sprawdza poprawność wszystkich dane dla komumn //private
		core::setError();
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
					$item['type'] = 'integer';
					$return[$item['name']] = (int)$arrData[$item['name']];
					break;
				case 'boolean':
					$item['type'] = 'boolean'; 
					$return[$item['name']] = boolval($arrData[$item['name']]);
					$item['length'] = 1;
					break;
				default:
					$return[$item['name']] = $arrData[$item['name']];
					break;
			}
			if(gettype($return[$item['name']]) <> $item['type'] and $item['type'] <> 'text')
				return core::setError(52, 'error data type', 'column: '.$item['name'].', type: '.$item['type'].' ('.gettype($arrData[$item['name']]).')');
			if(strlen($return[$item['name']]) > $item['length'] and $item['type'] <> 'text')
				return core::setError(53, 'error data length', 'column: '.$item['name'].', length: '.strlen($arrData[$item['name']]).'/'.$item['length']);
		}
		return $return;
	}
	public function ___checkTable(string $tableName){ //sprawdzenie poprawności tabeli			//private
		core::setError();
		$this->___log(['activeConnect' => $this->activeConnect]);
		if($this->activeConnect === null)
			return core::setError(3, 'connection not exists');
		if(!file_exists($this->connection[$this->activeConnect]['path'].$tableName.'.fdb'))
			return core::setError(20, 'table is not already exists');
		return true;
	}
	public function ___log($data, $title=''){ //wyświetlenie logu								//private
		core::setError();
		if($this->advencedLog === false)
			return false;
		core::$library->debug->print_r($data, false, 'fdbAdvInfo: '.$title);
	}
	public function ___columnDataExplode(string $data){ //generowanie tabeli kolumny			//private
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
				case 'boolean':
					$explode[1] = 'boolean';
					$return['length'] = 1;
					$return['data'] = (int)0;
					break;
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
	public function ___convertDataType(array $data, array $column){								//private
		core::setError();
		foreach($column as $item){
			switch($item['type']){
				case 'string':
				case 'text':
					foreach($data as $id => $item2){
						if(!isset($data[$id][$item['name']]))
							return core::setError(21, 'column not found', 'name: '.$item['name']);
						$data[$id][$item['name']] = strval($item2[$item['name']]);
					}
					break;
				case 'integer':
					foreach($data as $id => $item2){
						if(!isset($data[$id][$item['name']]))
							return core::setError(21, 'column not found', 'name: '.$item['name']);
						$data[$id][$item['name']] = (int)$item2[$item['name']];
					}
					break;
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
	public function ____saveFile(string $tableName, array $data){ 								//private
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
	public function ____readFile(string $tableName, string $type = 'all'){						//private
		core::setError();
		$readFile = file($this->connection[$this->activeConnect]['path'].$tableName.'.fdb');
		$returnData = ['option' => null, 'column' => null, 'data' => null];
		switch($type){
			case "all":
			case "option":
				$returnData['option'] = json_decode(trim($readFile[0]), true);
				$returnData['option']['autoincrement'] = json_decode($this->____decrypt($returnData['option']['autoincrement']), true);
				if($type === "option") return $returnData['option'];
			case "column":
				$returnData['column'] = json_decode($this->____decrypt(trim($readFile[1])), true);
				if(!is_array($returnData['column'])) $returnData['column'] = []; //naprawa
				if($type === "column") return $returnData['column'];
			case "data":
				$returnData['data'] = json_decode($this->____decrypt(trim($readFile[2])), true);
				if(!is_array($returnData['data'])) $returnData['data'] = []; //naprawa
				if($type === "data") return $returnData['data'];
		}
		return $returnData;
	}
	public function ____decrypt($string){														//private
		$password = $this->connection[$this->activeConnect]['pass']; //pobranie hasła
		return core::$library->crypt->decrypt($string, $password);
	}
}
?>