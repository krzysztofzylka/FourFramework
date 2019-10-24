<?php
return $this->db_old = new class(){ 
	public $version = '1.0.2'; 
	public $tableVersion = '1.0'; 
	public $cryptTable = true; 
	public $path = ''; 
	private $password = null; 
	private $regexp = [
		'tableName' => 'a-zA-Z0-9-_'
	]; 
	public function __construct(){ 
		core::setError(); 
		$this->setDBPath(core::$path['base'].'db/'); 
	}
	public function createTable(string $name, array $column) : bool{ 
		core::setError(); 
		$fileName = htmlspecialchars(basename($name)); 
		$path = $this->path.$fileName.'.FDB'; 
		if(file_exists($path)) 
			return core::setError(3, 'database is already exists'); 
		$data = [ 
			'option' => [
				'name' => $name,
				'version' => $this->tableVersion,
				'crypt' => $this->cryptTable,
				'autoincrement' => [
					'ai' => false,
					'id' => -1,
					'count' => 0
				]
			],
			'column' => [
				'count' => 0
			],
			'data' => [
			
			]
		];
		foreach($column as $item){ 
			if(!isset($item['name'])) 
				return core::setError(1, 'error column name'); 
			$column = [ 
				'name' => $item['name'],
				'type' => !isset($item['type'])?'string':$item['type'],
				'length' => !isset($item['length'])?11:(int)$item['length']
			];
			if(isset($item['autoincrement'])){ 
				$data['option']['autoincrement']['ai'] = true;
				$data['option']['autoincrement']['id'] = $data['column']['count'];
				$data['option']['autoincrement']['count'] = 1;
			}
			$data['column'][$data['column']['count']] = $column; 
			$data['column']['count']++; 
		}
		$this->_saveFile($name, $data); 
		if(!file_exists($path))
			return core::setError(2, 'error create database file'); 
		return true;
	}
	public function setDBPath(string $path) : void{ 
		core::setError(); 
		$this->path = $path; 
		if(!file_exists($path)) 
			mkdir($path, 0700, true); 
		$passwordPath = $path.'psswd.php'; 
		if(!file_exists($passwordPath)){ 
			touch($passwordPath); 
			$generatePassword = core::$library->string->generateString(25, [true, true, true, false]); 
			$generateFileString = '<?php return \''.$generatePassword.'\'; ?>'; 
			file_put_contents($passwordPath, $generateFileString); 
			$this->password = $generatePassword;
		}else
			$this->password = include($passwordPath); 
		return; 
	}
	public function addData(string $name, array $data) : bool{ 
		core::setError(); 
		$readData = $this->_readFile($name); 
		if(core::$error[0] > -1) 
			return core::setError(1, 'error read table file');
		$newData = []; 
		$ai = $readData['option']['autoincrement']; 
		foreach($readData['column'] as $id => $item){ 
			if(is_array($item)){ 
				$dane = @$data[$item['name']]; 
				if($ai['ai'] == true and $ai['id'] == $id) 
					$dane = (int)$ai['count'];
				$newData[$item['name']] = $dane; 
				
				$columnType = $item['type']; 
				if($columnType == 'int') 
					$columnType = 'integer';
				if(gettype($dane) <> $columnType) 
					return core::setError(2, 'error data type', 'column: '.$item['name'].', type: '.$columnType.' ('.gettype($dane).')'); 
				
				$columnLength = $item['length'];
				$dataLength = strlen($dane);
				if($dataLength > $columnLength) 
					return core::setError(3, 'error data length', 'column: '.$item['name'].', length: '.$dataLength.'/'.$columnLength); 
			}
		}
		if($ai['ai']) 
			$readData['option']['autoincrement']['count']++; 
		array_push($readData['data'], $newData); 
		$this->_saveFile($name, $readData); 
		return true;
	}
	public function readData(string $name, string $search = ''){ 
		core::setError(); 
		$readData = $this->_readFile($name); 
		if(core::$error[0] > -1) 
			return core::setError(1, 'error read table file'); 
		$data = $readData['data']; 
		
		if($search <> ''){ 
			$data = $this->_search($data, $search);
			$data = array_values(array_filter($data)); 
		}
		return $data;
	}
	public function deleteTable(string $name) : bool{ 
		core::setError(); 
		$fileName = htmlspecialchars(basename($name)); 
		$path = $this->path.$fileName.'.FDB'; 
		if(!file_exists($path)) 
			return core::setError(1, 'table file not exists'); 
		unlink($path); 
		return true; 
	}
	public function request(string $script){ 
		core::setError(); 
		$regexp = [
			'(CREATE TABLE) (|\'|\"|\`)([a-zA-Z0-9]+)(|\'|\"|\`) {(.+)}', 
			'(ADD DATA TO) [|\'|\"|\`](.+)[|\'|\"|\`] \((.+)\) VALUES \((.+)\)', 
			'(SELECT) (.+?)? ?(FROM) ["|\'|`]?(['.$this->regexp['tableName'].']+)["|\'|`]? ?(WHERE (.+))?', 
			'(ADVENCED) (GET|SET) (.+) FROM (['.$this->regexp['tableName'].']+) ?(OPTION+ ?(.+))?' 
		]; 
		foreach($regexp as $reg){ 
			preg_match_all('/'.$reg.'/ms', $script, $matches, PREG_SET_ORDER, 0); 
			if(count($matches) > 0) 
				return $this->__request($matches); 
		}
		return false; 
	}
	private function _search(array $data, string $search) : array{ 
		$explode = explode(' and ', $search); 
		foreach($explode as $item){ 
			
			$exp = $this->_searchExplode($item, '='); 
			if($exp <> false){ 
				foreach($data as $dataID => $dataData){ 
					if($dataData[$exp[0]] <> $exp[1]) 
						unset($data[$dataID]); 
				}
			}
			
			$exp = $this->_searchExplode($item, '%'); 
			if($exp <> false){ 
				foreach($data as $dataID => $dataData){ 
					if(core::$library->string->strpos($dataData[$exp[0]], $exp[1]) == -1) 
						unset($data[$dataID]); 
				}
			}
		}
		return $data;
	}
	private function _searchExplode($string, $exp){ 
		$explode = explode($exp, $string, 2); 
		if($explode[0] == $string)
			return false; 
		return $explode; 
	}
	private function _saveFile(string $name, array $data){ 
		core::setError(); 
		$fileName = htmlspecialchars(basename($name)); 
		$path = $this->path.$fileName.'.FDB'; 
		if(!file_exists($path)) 
			touch($path); 
		$data = json_encode($data['option']).PHP_EOL
				.json_encode($data['column']).PHP_EOL
				.json_encode($data['data']); 
		file_put_contents($path, $data); 
	}
	private function _readFile(string $name){ 
		core::setError(); 
		$fileName = htmlspecialchars(basename($name)); 
		$path = $this->path.$fileName.'.FDB'; 
		if(!file_exists($path)) 
			return core::setError(1, 'error load file');
		$read = file_get_contents($path); 
		$read = explode(PHP_EOL, $read);
		$data = [
			'option' => json_decode($read[0], true),
			'column' => json_decode($read[1], true),
			'data' => json_decode($read[2], true)
		];
		return $data;
	}
	private function __request($array){ 
		core::setError(); 
		$array = $array[0]; 
		switch($array[1]){ 
			case "CREATE TABLE": 
				preg_match_all('/[\'|\"|\`](.+?)[\'|\"|\`] ((int|string|bool)(\([0-9]+\))|text)( |,)?(autoincrement)?/ms', $array[5], $matchesData, PREG_SET_ORDER, 0);
				$column = []; 
				foreach($matchesData as $item){ 
					$add = [
						'name' => $item[1], 
						'autoincrement' => null, 
						'type' => null, 
						'length' => null 
					];
					if(is_int(array_search($item[2], ['text']))) 
						$add['type'] = $item[2]; 
					else{ 
						$add['type'] = $item[3]; 
						$add['length'] = substr($item[4], 1, strlen($item[4])-2); 
					}
					foreach($item as $text){ 
						if($text == "autoincrement") 
							$add['autoincrement'] = true; 
					}
					array_push($column, $add); 
				}
				return $this->createTable($array[3], $column); 
				break;
			case "ADD DATA TO": 
				$addData = []; 
				preg_match_all('/[\'|"|`]?([a-zA-Z0-9]+)[\'|"|`]?,?/s', $array[3], $name, PREG_SET_ORDER, 0); 
				preg_match_all('/[\'|"|`]?([a-zA-Z0-9]+)[\'|"|`]?,?/s', $array[4], $data, PREG_SET_ORDER, 0); 
				foreach($name as $id => $item) 
					$addData[$item[1]] = $data[$id][1]; 
				return $this->addData($array[2], $addData); 
				break;
			case "SELECT":
				$name = $array[4]; 
				$where = ''; 
				if(count($array) >= 6) 
					$where = $array[6]; 
				return $this->readData($name, $where); 
				break;
			case 'ADVENCED':
				switch($array[2]){
					case 'GET':
						switch($array[3]){
							case 'column':
								$read = $this->_readFile('test2');
								return $read['column'];
								break;
						}
						break;
					case 'SET':
						break;
				}
			break;
		}
		return false; 
	}
}
?>