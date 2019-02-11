<?php
return new class($this){
	protected $core;
	public $temp = true;
	protected $temp_array = [];
	public function __construct($obj){
		$this->core = $obj;
	}
	//write to database
	public function write(string $db_name, string $name, string $value){
		$this->core->returnError();
		$read = $this->_readFile($db_name);
		if(!$read) $read = array();
		$read[$name] = $value;
		return $this->_saveFile($db_name, $read);
	}
	//read from database
	public function read(string $db_name, string $name, $default=null){
		$this->core->returnError();
		$read = $this->_readFile($db_name);
		if(!$read) return $default==null?false:$default;
		if(isset($read[$name])) return $read[$name];
		return $default;
	}
	//delete data from database
	public function del(string $db_name, string $name){
		$this->core->returnError();
		$read = $this->_readFile($db_name);
		if(!$read) return $this->core->returnError(1, 'error save to file'); //error 1
		unset($read[$name]);
		if(count($read) == 0){
			$file = $this->core->path['file_ext_db_base'];
			$zip = new ZipArchive();
			if($zip->open($file, ZipArchive::CREATE) !== true) return false;
			$zip->deleteName($db_name.'.sdb');
			$zip->close();
			return true;
		}
		return $this->_saveFile($db_name, $read);
	}
	//check data in database
	public function check(string $db_name, string $name){
		$this->core->returnError();
		$read = $this->_readFile($db_name);
		if(!$read) return return $this->core->returnError(1, 'error read from file'); //error 1
		return isset($read[$name])?true:false;
	}
	//save array to file
	private function _saveFile(string $db_name, array $array){
		$this->core->returnError();
		$write = serialize($array);
		$zip = new ZipArchive();
		if($zip->open($this->core->path['file_ext_db_base'], ZipArchive::CREATE) !== true) return false;
		$zip->addFromString($db_name.'.sdb', $write);
		$zip->close();
		return true;
	}
	//read array from file
	private function _readFile(string $db_name){
		$this->core->returnError();
		if($this->temp){
			if(isset($this->temp_array[$db_name])){
				$read = $this->temp_array[$db_name];
				$this->core->wlog('Read data from database \''.$db_name.'\' (temponary)', 'db', 'message');
				return $read;
			}
		}
		$zip = new ZipArchive();
		if($zip->open($this->core->path['file_ext_db_base']) === true){
			$string = $zip->getFromName($db_name.'.sdb');
			$zip->close();
			$decode = unserialize($string);
		}else return $this->core->returnError(1, 'error open zip file', ['path' => $this->core->path['file_ext_db_base']]); //error 1;
		if($this->temp) $this->temp_array[$db_name] = $decode;
		//return array
		return $decode;
	}
	//read array from database
	public function readArray(string $db_name){
		$this->core->returnError();
		$read = $this->_readFile($db_name);
		if(!$read) return false;
		return $read;
	}
	//show manager
	public function manager(){
		$this->core->returnError();
		include($this->core->path['dir_ext_db'].'manager.php');
	}
	//get database list
	public function dbList(){
		$this->core->returnError();
		$arr = [];
		$zip = new ZipArchive();
		if ($zip->open($this->core->path['file_ext_db_base']) == TRUE) {
			for ($i = 0; $i < $zip->numFiles; $i++) {
				$filename = $zip->getNameIndex($i);
				array_push($arr, $filename);
			}
		}
		return $arr;
	}
	//debug function
	public function __debugInfo(){
		$this->core->returnError();
		return [
			'db_path' => $this->core->path['file_ext_db_base'],
			'temp' => [
				'active' => $this->temp?'true':'false',
				'count' => $this->temp?count($this->temp_array):'***disable***',
				'list' => $this->temp?implode(',',array_keys($this->temp_array)):'***disable***',
			],
		];
	}
}
?>