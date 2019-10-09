<?php
return $this->db = new class(){ //create db library
	public $version = '1.0'; //version
	public $tableVersion = '1.0'; //table version
	public $cryptTable = false; //crypt db table
	public $path = ''; //database path
	private $password = null; //password
	public function __construct(){ //main function
		core::setError(); //clear error
		$this->setDBPath(core::$path['base'].'db/'); //generate database path
	}
	public function createTable(string $name, array $column) : bool{ //tworzenie tabeli
		core::setError(); //clear error
		$fileName = htmlspecialchars(basename($name)); //generowanie nazwy pliku
		$path = $this->path.$fileName.'.FDB'; //generowanie ścieżki bazy danych
		$data = [ //generowanie tablicy
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
		foreach($column as $item){ //dodawanie kolumn do danych
			if(!isset($item['name'])) //jezeli kolumna nie zawiera nazwy
				return core::setError(1, 'error column name');
			$column = [ //generowanie kolumny
				'name' => $item['name'],
				'type' => !isset($item['type'])?'string':$item['type'],
				'length' => !isset($item['length'])?11:(int)$item['length']
			];
			if(isset($item['autoincrement'])){ //jeżeli istnieje autoodliczanie
				$data['option']['autoincrement']['ai'] = true;
				$data['option']['autoincrement']['id'] = $data['column']['count'];
				$data['option']['autoincrement']['count'] = 1;
			}
			$data['column'][$data['column']['count']] = $column; //dodawanie kolumny do tabeli
			$data['column']['count']++; //dodawanie licznika
		}
		$this->_saveFile($name, $data); //save to file
		if(file_exists($path))
			return core::setError(2, 'database is already exists'); //return error
		return true;
	}
	public function setDBPath(string $path) : void{ //set db path
		core::setError(); //clear error
		$this->path = $path; //set path
		if(!file_exists($path)) //if not exists
			mkdir($path, 0700, true); //create dir
		$passwordPath = $path.'psswd.php'; //generate password path
		if(!file_exists($passwordPath)){ //if not exists
			touch($passwordPath); //create file
			$generatePassword = core::$library->string->generateString(25, [true, true, true, false]); //generate crypt password
			$generateFileString = '<?php return \''.$generatePassword.'\'; ?>'; //generate PHP string
			file_put_contents($passwordPath, $generateFileString); //save return function to file
			$this->password = $generatePassword;
		}else
			$this->password = include($passwordPath); //include password file
		return; //return empty
	}
	public function addData(string $name, array $data) : bool{ //add data
		core::setError(); //clear error
		$readData = $this->_readFile($name); //read data
		if(core::$error[0] > -1) //if error read file
			return core::setError(1, 'error read table file');
		$newData = []; //tablica z danymi które zostaną dodane
		$ai = $readData['option']['autoincrement']; //tabela z AI
		foreach($readData['column'] as $id => $item){ //pętla kolumn
			if(is_array($item)){ //jeżeli jest tabelą
				$dane = @$data[$item['name']]; //dane do zmiennej
				if($ai['ai'] == true and $ai['id'] == $id) //autoincrement
					$dane = (int)$ai['count'];
				$newData[$item['name']] = $dane; //dodawanie danych do tabeli
				//check data type and length
				$columnType = $item['type']; //column type
				if($columnType == 'int') //integer to int
					$columnType = 'integer';
				if(gettype($dane) <> $columnType) //data type error
					return core::setError(2, 'error data type', 'column: '.$item['name'].', type: '.$columnType.' ('.gettype($dane).')'); //return error
				//check data length
				$columnLength = $item['length'];
				$dataLength = strlen($dane);
				if($dataLength > $columnLength) //check length
					return core::setError(3, 'error data length', 'column: '.$item['name'].', length: '.$dataLength.'/'.$columnLength); //return error
			}
		}
		if($ai['ai']) //if autoincrement
			$readData['option']['autoincrement']['count']++; //add data
		array_push($readData['data'], $newData); //add data to array
		$this->_saveFile($name, $readData); //save data to file
		return true;
	}
	public function readData(string $name, string $search = ''){ //read data
		core::setError(); //clear error
		$readData = $this->_readFile($name); //read data
		if(core::$error[0] > -1) //if error read file
			return core::setError(1, 'error read table file'); //return error
		$data = $readData['data']; //get data to var
		//search
		if($search <> ''){ //if not exists
			$explode = explode(' and ', $search); //explode search
			foreach($explode as $item){ //search loop
				//=
				$exp = $this->_searchExplode($item, '='); //search explode item
				if($exp <> false){ //return <> false
					foreach($data as $dataID => $dataData){ //foreach data
						if($dataData[$exp[0]] <> $exp[1]) //search
							unset($data[$dataID]); //delete item
					}
				}
			}
			$data = array_values(array_filter($data)); //reindex and clear
		}
		return $data;
	}
	private function _searchExplode($string, $exp){ //explode function for search
		$explode = explode($exp, $string, 2); //explode string
		if($explode[0] == $string)
			return false; //if error
		return $explode; //return array
	}
	private function _saveFile(string $name, array $data){ //save database file
		core::setError(); //clear error
		$fileName = htmlspecialchars(basename($name)); //generowanie nazwy pliku
		$path = $this->path.$fileName.'.FDB'; //generowanie ścieżki bazy danych
		if(!file_exists($path)) //if file not exists
			touch($path); //create empty file
		file_put_contents($path, json_encode($data)); //write data to file
	}
	private function _readFile(string $name){ //read database file
		core::setError(); //clear error
		$fileName = htmlspecialchars(basename($name)); //generowanie nazwy pliku
		$path = $this->path.$fileName.'.FDB'; //generowanie ścieżki bazy danych
		if(!file_exists($path)) //if not exists
			return core::setError(1, 'error load file');
		return json_decode(file_get_contents($path), true); //return data
	}
}
?>