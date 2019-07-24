<?php
return $this->db = new class(){ //create db library
	public $tableVersion = '1.1'; //table version
	public $cryptTable = false; //crypt db table
	public $path = ''; //database path
	public function __construct(){ //main function
		core::setError(); //clear error
		$this->setDBPath(core::$path['base'].'db/'); //generate database path
	}
	public function createTable(string $name, array $data) : bool{ //create table
		core::setError(); //clear error
		$name = basename(htmlspecialchars($name)); //protect name
		$path = $this->path.$name.'.FDB'; //table file path
		if(file_exists($path)) //if exists
			return core::setError(1, 'a table with this name already exists'); //return error 1
		$line = [
			'option' => [
				'name' => '',
				'version' => $this->tableVersion,
				'crypt' => $this->cryptTable,
				'autoincrement' => false,
			], //option
			'column' => [], //column
			'data' => [] //data
		];
		$line['column'] = $data;
		foreach($data as $name => $column){ //column loop
			if(isset($column['autoincrement'])) //autoincrement isset
				if($column['autoincrement'] == true){ //if true
					$line['option']['autoincrement'] = [
						'name' => $name
					]; //set autoincrement
					$line['column'][$name]['counter'] = 1; //create AI counter
				}
		}
		$this->_dbFileWrite($path, $line); //write db to file
		return true; //return success
	}
	public function addData(string $tableName, array $data) : bool{ //add data
		$path = $this->path.$tableName.'.FDB'; //table file path
		$read = $this->_dbFileRead($path); //read file
		$add = []; //add data array
		foreach($read['column'] as $name => $option){ //column loop
			if(isset($option['autoincrement'])){ //if autoincrement
				$data[$name] = (int)$option['counter']; //write counter
				$read['column'][$name]['counter'] += 1; //add counter
			}
			if($option['type'] == "int") //replace int
				$option['type'] = "integer"; //set type
			if(!isset($data[$name])) //if not exists data
				return core::setError(1, 'no find data in array', 'data name: '.$name); //return error 1
			if(strlen($data[$name]) > $option['length']) //check length
				return core::setError(2, 'the string is too long (max length: '.$option['length'].')', 'data name: '.$name); //return error 2
			if(gettype($data[$name]) <> $option['type']) //check data type
				return core::setError(3, 'wrong data type', 'data name: '.$name.' ('.gettype($data[$name]).')'); //return error 3
			$add[$name] = $data[$name]; //add column
		}
		array_push($read['data'], $add); //add data
		$this->_dbFileWrite($path, $read); //write data
		return true; //return success
	}
	public function setDBPath(string $path) : void{ //set db path
		core::setError(); //clear error
		$this->path = $path; //set path
		if(!file_exists($path)) //if not exists
			mkdir($path, 0777, true); //create dir
		return; //return empty
	}
	public function request(string $tableName){ //request table
		return new class($this, $tableName){ //create class
			protected $mainClass; //main class
			protected $tableName; //table name
			protected $path; //table path
			public $tableOption = null; //table option
			public function __construct($mainClass, $tableName){ //main function
				$this->mainClass = $mainClass; //set main class
				$this->tableName = basename($tableName); //set table name
				$this->path = $mainClass->path.basename($tableName).'.FDB'; //set table path
				if(!file_exists($this->path)) //if not exists
					die('No find `'.basename($tableName).'` table in database'); //die
				$this->tableOption = $mainClass->_dbFileRead($this->path, "option"); //read table pption
			}
			public function fetch(array $where = []){ //fetch function
				$column = $this->mainClass->_dbFileRead($this->path, 'column'); //read column
				$data = $this->mainClass->_dbFileRead($this->path, 'data'); //read data
				foreach($where as $item){ //where loop
				if(count($item) == 3){ //check where
					switch($item[1]){ //switch $item[1]
						case '=': //=
							foreach($data as $id => $item2){ //data loop
								if($item2[$item[0]] <> $item[2]) //false
									unset($data[$id]); //delete item
							}
							break;
					}
				}else //error
					return core::setError(1, 'reading error of the where condition'); //return error 1
			}
				return [
					'count' => count($data),
					'data' => $data,
					'where' => count($where)==0?false:$where,
				]; //return data
			}
		};
	}
	public function _dbFileWrite(string $path, array $writeData){ //write array to database
		$write = json_encode($writeData['option']).PHP_EOL.
				 json_encode($writeData['column']).PHP_EOL.
				 json_encode($writeData['data']); //generate write string
		file_put_contents($path, $write); //write
	}
	public function _dbFileRead(string $path, string $type = "all"){ //read all data (or type) from database
		if(!file_exists($path)) //if not exists
			return core::setError(1, 'file does not exist'); //return error 1
		switch($type){ //switch type
			default:
			case 'all': //all data
				$read = file_get_contents($path); //read file
				$explode = explode(PHP_EOL, $read); //explode to file
				$return = [
					'option' => json_decode($explode[0], true),
					'column' => json_decode($explode[1], true),
					'data' => json_decode($explode[2], true),
				]; //generate return string
				return $return; // return array
				break;
			case 'option':
				return json_decode(file($path)[0], true);
				break;
			case 'column':
				return json_decode(file($path)[1], true);
				break;
			case 'data':
				return json_decode(file($path)[2], true);
				break;
		}
	}
}
?>