<?php
/* obsługa wersji baz danych:
	addData: 			1.0
	getColumnAdvList:	1.0
	getData: 			1.0
	updateData: 		1.0
	deleteData: 		1.0
	setOption:			1.0
	checkTable:			1.0
*/
return $this->db = new class($this->core){
	protected $core;
	public $version = '1.0.1';
	public $tableVersion = '1.0';
	public $cryptDB = true;
	public $path;
	private $lastId;
	private $hash = '';
	public function __construct($obj){
		$this->crypt = $obj->crypt;
		$this->core = $obj;
		$this->path = $obj->path['dir_db'];
		$this->setDatabaseDir();
	}
	public function createTable(string $tableName, array $column) : bool{
		$this->core->returnError();
		$path = $this->path.$tableName.'.FDB';
		if(file_exists($path))
			return $this->core->returnError(1, 'there is already a table with this name');
		$option = [
			'name' => $tableName,
			'version' => $this->tableVersion,
			'crypt' => $this->cryptDB,
			'hash' => false,
		];
		$option2 = [
			'lastUse' => date('Y-m-d H:i:s.v'),
			'autoincrement' => null
		];
		$col = [];
		$data = [];
		$i = 0;
		foreach($column as $item){
			$name = trim($item);
			$ai = false;
			if(strpos(strtoupper($name), 'AUTOINCREMENT')){
				$name = trim(str_ireplace('AUTOINCREMENT', '', $name));
				$ai = true;
				if($option2['autoincrement'] == null)
					$option2['autoincrement'] = ['name' => $name, 'id' => $i];
				else
					return $this->core->returnError(2, 'there can be only one autoincrement field');
			}
			$item = [
				'name' => $name,
				'autoincrement' => $ai,
				'count' => $ai?1:false,
			];
			array_push($col, $item);
			$i++;
		}
		$fileWrite = [
			$option,
			$option2,
			$col,
			$data
		];
		$this->__saveDbFile($tableName, $fileWrite);
		chmod($path, 0600);
		return true;
	}
	public function addData(string $tableName, array $data) : bool{
		$this->core->returnError();
		$read = $this->__readDbFile($tableName, true);
		if($read === false)
			return $this->core->returnError(1, $this->core->lastError['name']);
		switch($read[0]["version"]){
			case "1.0":
				$columnList = $this->__columnList($read[2]);
				foreach(array_keys($data) as $name)
					if(array_search($name, $columnList) === false)
						return $this->core->returnError(2, 'not found column (name: '.$name.')');
				if($read[1]['autoincrement'] <> null){
					$data[$read[1]['autoincrement']['name']] = $read[2][(int)$read[1]['autoincrement']['id']]['count'];
					$read[2][(int)$read[1]['autoincrement']['id']]['count']++;
				}
				array_push($read[3], $data);
				$this->__saveDbFile($tableName, $read);
				return true;
				break;
		}
	}
	public function getColumnAdvList(string $tableName){
		$this->core->returnError();
		$option = $this->__readDbFile($tableName, false, 0);
		$read = $this->__readDbFile($tableName, false, 2);
		if($read === false)
			return $this->core->returnError(1, $this->core->lastError['name']);
		switch($option["version"]){
			case "1.0":
				return $read;
				break;
		}
		return false;
	}
	public function getData(string $tableName, array $where = [], bool $multi = true){
		$this->core->returnError();
		$read = $this->__readDbFile($tableName, true);
		if($read === false)
			return $this->core->returnError(1, $this->core->lastError['name']);
		switch($read[0]["version"]){
			case "1.0":
				$data = $read[3];
				$data = $this->__where($data, $where);
				if($multi)
					return $data;
				else{
					if(count($data) > 0){
						$data = $data[array_keys($data)[0]];
						return $data;
					}else return false;
				}
				break;
		}
		return false;
	}
	public function updateData(string $tableName, array $where = [], array $set = []){
		$this->core->returnError();
		$option = $this->__readDbFile($tableName, false, 0);
		switch($option["version"]){
			case "1.0":
				$update = 0;
				$read = $this->__readDbFile($tableName, true);
				if($read === false)
					return $this->core->returnError(1, $this->core->lastError['name']);
				$data = $read[3];
				$where = $this->__where($data, $where, 'returnKey');
				foreach($where as $item){
					foreach($set as $item2){
						$explode = explode('=', $item2, 2);
						if(count($explode) == 2){
							$data[$item][$explode[0]] = $explode[1];
							continue;
						}
					}
					$read[3] = $data;
					$update += 1;
				}
				$this->__saveDbFile($tableName, $read);
				return $update;
				break;
		}
		return false;
	}
	public function deleteData(string $tableName, array $where = []){
		$this->core->returnError();
		$delete = 0;
		$read = $this->__readDbFile($tableName, true);
		if($read === false)
			return $this->core->returnError(1, $this->core->lastError['name']);
		switch($read[0]["version"]){
			case "1.0":
				$data = $read[3];
				$where = $this->__where($data, $where, 'returnKey');
				foreach($where as $id){
					unset($data[$id]);
					$delete += 1;
				}
				$read[3] = $data;
				$this->__saveDbFile($tableName, $read);
				return $delete;
				break;
		}
		return false;
	}
	public function script(string $script){
		$this->core->returnError();
		$main = explode(' ', $script, 2);
		if(count($main) < 2)
			return $this->core->returnError(1, 'error script syntax');
		switch($main[0]){
			//CREATE TABLE {table name} ({column name}, {column2 name}, {column3 name})
			case 'CREATE': //CREATE
				$create = explode(' ', $main[1], 2);
				if(count($create) < 2)
					return $this->core->returnError(1, 'error script syntax');
				switch($create[0]){
					case 'TABLE': //CREATE TABLE
						$create2 = explode(' ', $create[1], 2);
						$column = $this->core->library->string->between($create2[1], '(', ')');
						$column = explode(',', $column);
						for($i=0;$i<count($column);$i++)
							$column[$i] = trim($column[$i]);
						return $this->createTable($create2[0], $column);
						break;
				}
				break;
			//ADD {table name} DATA
			case 'ADD': //ADD
				$add = explode(' ', $main[1], 3);
				switch($add[1]){
					case 'DATA': //ADD DATA
						$val = explode(' VALUES ', $add[2]);
						$col = trim($val[0]);
						$data = trim($val[1]);
						//col check
						$left = substr($col, 0, 1);
						$right = substr($col, strlen($col)-1, strlen($col));
						if($left <> '(' and $right <> ')')
							return $this->core->returnError(1, 'error script syntax');
						$col = substr($col, 1, strlen($col)-2);
						//data check
						$left = substr($data, 0, 1);
						$right = substr($data, strlen($data)-1, strlen($data));
						if($left <> '(' and $right <> ')')
							return $this->core->returnError(1, 'error script syntax');
						$data = substr($data, 1, strlen($data)-2);
						//column
						$column = explode(',', $col);
						for($i=0; $i<count($column); $i++)
							$column[$i] = trim($column[$i]);
						//data
						preg_match_all('~"(.+?)"~', $data, $data);
						$data = $data[1];
						$data_output = [];
						if(count($column) <> count($data))
							return $this->core->returnError(1, 'error script syntax');
						for($i=0; $i<count($column); $i++)
							$data_output[$column[$i]] = $data[$i];
						return $this->addData($add[0], $data_output);
						break;
				}
				break;
			//DELETE {table name} WHERE {where1} and {where2} and {where3}
			case 'DELETE':
				$del = explode(' ', $main[1], 3);
				switch($del[1]){
					case 'WHERE':
						$where = explode(' and ', $del[2]);
						return $this->deleteData($del[0], $where);
						break;
				}
				break;
		}
	}
	private function __where(array $data, array $where, $type='unset'){
		$this->core->returnError();
		$temp = [];
		foreach($data as $key => $element){
			foreach($where as $key2 => $element2){
				$explode = explode('>=', $element2, 2);
				if(count($explode) == 2){
					if($element[$explode[0]] < $explode[1]){
						switch($type){
							case 'unset':
								unset($data[$key]);
								break;
							case 'returnKey':
								array_push($temp, $key);
								break;
						}
					}
					continue;
				}
				$explode = explode('<=', $element2, 2);
				if(count($explode) == 2){
					if($element[$explode[0]] > $explode[1]){
						switch($type){
							case 'unset':
								unset($data[$key]);
								break;
							case 'returnKey':
								array_push($temp, $key);
								break;
						}
					}
					continue;
				}
				$explode = explode('=', $element2, 2);
				if(count($explode) == 2){
					if($element[$explode[0]] <> $explode[1]){
						switch($type){
							case 'unset':
								unset($data[$key]);
								break;
							case 'returnKey':
								array_push($temp, $key);
								break;
						}
					}
					continue;
				}
				$explode = explode('<>', $element2, 2);
				if(count($explode) == 2){
					if($element[$explode[0]] == $explode[1]){
						switch($type){
							case 'unset':
								unset($data[$key]);
								break;
							case 'returnKey':
								array_push($temp, $key);
								break;
						}
					}
					continue;
				}
				$explode = explode('>', $element2, 2);
				if(count($explode) == 2){
					if($element[$explode[0]] <= $explode[1]){
						switch($type){
							case 'unset':
								unset($data[$key]);
								break;
							case 'returnKey':
								array_push($temp, $key);
								break;
						}
					}
					continue;
				}
				$explode = explode('<', $element2, 2);
				if(count($explode) == 2){
					if($element[$explode[0]] >= $explode[1]){
						switch($type){
							case 'unset':
								unset($data[$key]);
								break;
							case 'returnKey':
								array_push($temp, $key);
								break;
						}
					}
					continue;
				}
				$explode = explode('%', $element2, 2);
				if(count($explode) == 2){
					if(strpos($element[$explode[0]], $explode[1]) === false){
						switch($type){
							case 'unset':
								unset($data[$key]);
								break;
							case 'returnKey':
								array_push($temp, $key);
								break;
						}
					}
					continue;
				}
			}
		}
		switch($type){
			case 'unset':
				return $data;
			break;
			case 'returnKey':
				$tmp = [];
				$keys = array_diff(array_keys($data), $temp);
				foreach($keys as $item){
					array_push($tmp, $item);
				}
				return $tmp;
				break;
		}
		
	}
	private function __columnList(array $array){
		$this->core->returnError();
		if(!is_array($array))
			return $this->core->returnError(1);
		$tmp = [];
		foreach($array as $column)
			array_push($tmp, $column['name']);
		return $tmp;
	}
	private function __saveDbFile(string $tableName, array $array, bool $autoData = true) : bool{
		$this->core->returnError();
		$crypt = $this->core->library->crypt;
		$path = $this->path.$tableName.'.FDB';
		$isCrypt = (bool)$array[0]['crypt'];
		if($isCrypt)
			$array[0]['hash'] = $crypt->crypt(true, $this->hash);
		$toFileString = "";
		if($autoData)
			$array[1]['lastUse'] = date('Y-m-d H:i:s.v');
		$maxLine = count($array);
		for($i=0;$i<$maxLine;$i++){
			$array[$i] = json_encode($array[$i]);
			if($isCrypt and $i>0)
				$array[$i] = $crypt->crypt($array[$i], $this->hash);
			$toFileString .= $array[$i];
			if($i < $maxLine-1)
				$toFileString .= PHP_EOL;
		}
		file_put_contents($path, $toFileString);
		return true;
	}
	private function __readDbFile(string $tableName, $array=false, $line=-1){
		$this->core->returnError();
		$path = $this->path.$tableName.'.FDB';
		$crypt = $this->core->library->crypt;
		if(!file_exists($path))
			return $this->core->returnError(1, 'file not exists');
		if($line == -1){
			$json = file_get_contents($path);
			$array = explode(PHP_EOL, $json);
			$isCrypt = false;
			for($i=0;$i<count($array);$i++){
				if($i > 0 and $isCrypt)
					$array[$i] = $crypt->decrypt($array[$i], $this->hash);
				$array[$i] = json_decode($array[$i], true);
				if(json_last_error() != JSON_ERROR_NONE)
					return $this->core->returnError(2, 'error syntax', 'syntax or password error');
				if($i == 0)
					$isCrypt = (bool)$array[$i]['crypt'];
			}
			if(!is_array($array[3]))
				$array[3] = [];
			return $array;
		}else{
			$header = json_decode(file($path)[0], true);
			if(isset($header['hash']))
				if($crypt->decrypt($header['hash'], $this->hash) <> true)
					return $this->core->returnError(3, 'Error read db file, incorect db');
			if($line == 0)
				return $header;
			return json_decode(($header['crypt'])?$crypt->decrypt(file($path)[(int)$line], $this->hash):file($path)[(int)$line], true);
		}
	}
	public function __DebugInfo(){
		return [
			'version' => $this->version,
			'path' => $this->path,
			'crypt' => $this->crypt
		];
	}
	public function setDatabaseDir($path=null) : bool{
		$this->core->returnError();
		$this->path = $path??$this->core->path['dir_base'].'db/';
		if(!file_exists($this->path)) mkdir($this->path);
		$path_hash = $this->path.'hash.php';
		if(file_exists($path_hash))
			$this->hash = include($path_hash);
		else
			$this->_generateDBHash();
		return true;
	}
	public function tableList(){
		$this->core->returnError();
		$list = [];
		$scan = scandir($this->path);
		foreach($scan as $name){
			if(strpos($name, '.FDB') === false)
				continue;
			$name = str_replace('.FDB', '', $name);
			array_push($list, $name);
		}
		return $list;
	}
	public function getDBInformaction(string $tableName){
		$this->core->returnError();
		$path = $this->path.$tableName.'.FDB';
		$return = [];
		$line1 = $this->__readDbFile($tableName, true, 0);
		$line2 = $this->__readDbFile($tableName, true, 1);
		$return['name'] = $line1['name'];
		$return['version'] = $line1['version'];
		$return['crypt'] = (bool)$line1['crypt'];
		$return['lastUse'] = $line2['lastUse'];
		$return['perms'] = substr(sprintf('%o', fileperms($path)), -4);
		return $return;
	}
	public function deleteTable(string $tableName) : bool{
		$this->core->returnError();
		$path = $this->path.$tableName.'.FDB';
		if(!file_exists($path))
			return $this->core->returnError(1, 'table not exists');
		unlink($path);
		if(file_exists($path))
			return $this->core->returnError(2, 'error delete file');
		return true;
	}
	public function setOption(string $tableName, string $type, string $adding=null){
		$this->core->returnError();
		$array = $this->__readDbFile($tableName, true);
		if($array === false)
			return $this->core->returnError(1, $this->core->lastError['name']);
		switch($array[0]["version"]){
			case "1.0":
				switch($type){
					case 'crypt':
						$array[0]['crypt'] = true;
						$this->__saveDbFile($tableName, $array);
						return true;
						break;
					case 'decrypt':
						$array[0]['crypt'] = false;
						$this->__saveDbFile($tableName, $array);
						return true;
						break;
					case 'clear':
						$array[3] = [];
						if($array[1]['autoincrement'] <> null)
							$array[2][$array[1]['autoincrement']['id']]['count'] = 1;
						$this->__saveDbFile($tableName, $array);
						return true;
						break;
				}
				break;
		}
		return false;
	}
	public function checkTable(string $tableName, bool $repair = true){
		$return = "";
		$path = $this->path.$tableName.'.FDB';
		if(!file_exists($path))
			return $this->core->returnError(1, 'table not exists', $path);
		$header = json_decode(file($path)[0], true);
		$version = isset($header['version'])?$header['version']:false;
		$crypt = $this->core->library->crypt;
		switch($version){
			case '1.0':
				$option = file($path)[1];
				$column = file($path)[2];
				$data = file($path)[3];
				//header -------------------------------------------- 5
				//repair header name
				if(!isset($header['name'])){
					$return .= 'no data name table in the header';
					if($repair){
						$header['name'] = $tableName;
						$return .= ' - repair: add data "'.$header['name'].'"';
					}
					$return .= '<br />';
				}
				//repair header crypt
				if(!isset($header['crypt'])){
					$return .= 'no data crypt table in the header';
					if($repair){
						$json = json_decode($option, true);
						if(json_last_error() == 0)
							$header['crypt'] = false;
						else{
							$header['crypt'] = true;
						}
						$return .= ' - repair: add data "'.($header['crypt']?'true':'false').'"';
					}
					$return .= '<br />';
				}
				//read (decrypt) option/column/data
				if((bool)$header['crypt']){
					$option = $crypt->decrypt($option, $this->hash);
					$column = $crypt->decrypt($column, $this->hash);
					$data = $crypt->decrypt($data, $this->hash);
				}
				$option = json_decode($option, true);
				$column = json_decode($column, true);
				$data = json_decode($data, true);
				//repair option lastUse
				if(!isset($option['lastUse'])){
					$return .= 'no data in option (lastUse)';
					if($repair){
						$option['lastUse'] = '0000-00-00 00:00:00.000';
						$return .= ' - repair: add data lastUse to option ("'.$option['lastUse'].'")';
					}
					$return .= '<br />';
				}
				//repair option autoincrement
				if(!isset($option['autoincrement'])){
					$return .= 'no data in option (autoincrement)';
					if($repair){
						$option['autoincrement'] = null;
						$i = 0;
						foreach($column as $item){
							if($item['autoincrement'] == true)
								$option['autoincrement'] = ['name' => $item['name'], 'id' => $i];
							$i++;
						}
						$return .= ' - repair: add autoincrement data ('.(is_array($option['autoincrement'])?'{array}':'null').')';
					}
					$return .= '<br />';
				}
				//check autoincrement item
				$i = 0;
				$find = true;
				foreach($column as $item){
					if(isset($item['autoincrement'])){
						if($item['autoincrement'] === true){
							$find = true;
							$ai = $option['autoincrement'];
							if($ai['name'] <> $item['name']){
								$find = false;
								$column[$i]['autoincrement'] = false;
								$column[$i]['count'] = false;
							}
							break;
						}
					}
					$i++;
				}
				if($find == false){
					$return .= 'repair item autoicrement -- clear autoincrement item and option autoincrement set "null"';
					$option['autoincrement'] = null;
					$return .= '<br />';
				}
				//repair data (arr)
				if(!is_array($data)){
					$return .= 'no data (array)';
					if($repair){
						$data = [];
						$return .= ' - repair: add array to data';
					}
				}
				//if repair - save data to file
				if($repair and $return <> ""){
					$array = [
						$header,
						$option,
						$column,
						$data
					];
					$this->__saveDbFile($tableName, $array);
				}
				//check chmod
				if(substr(sprintf('%o', fileperms($path)), -4) <> '0600'){
					$return .= 'chmod is not 0600 (act: '.substr(sprintf('%o', fileperms($path)), -4).')';
					if($repair){
						chmod($path, 0600);
						$return .= ' - repair chmod';
					}
					$return .= '<br />';
				}
				break;
			case false:
				return 'error reading header table';
				break;
			default:
				return 'wrong version ('.$version.')';
				break;
		}
		return $return;
	}
	public function _generateDBHash(){
		$path_hash = $this->path.'hash.php';
		$generate = $this->core->library->string->generateString(30);
		$this->hash = $generate;
		file_put_contents($path_hash, '<?php return \''.$this->hash.'\'; ?>');
	}
	public function _setHash(string $hash) : void{
		$this->hash = $hash;
		return;
	}
	private function _getConfig(string $tableName){
		return $this->__readDbFile($tableName, false, 0);
	}
}
?>