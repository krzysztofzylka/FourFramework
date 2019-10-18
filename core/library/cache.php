<?php
return $this->cache = new class(){ //create cache
	public $version = '1.0a'; //version
	private $dir = ''; //cache dir path
	public function __construct(){ //main function
		core::setError(); //clear error
		$this->dir = core::$path['base'].'cache/'; //generate cache path
		if(!file_exists($this->dir)) //if not exists
			mkdir($this->dir); //create cache dir
	}
	public function funcCache(string $name, string $function, int $time = 3600){ //read cache or load from function
		core::setError(); //clear error
		$config = $this->_loadConfig(); //read config
		$check = $this->__checkCache($name, $config); //check cache file
		if(!isset($config[$name]) or $check == false){ //if cache file not exists
			$callData = call_user_func($function); //call to function
			$this->writeCache($name, $callData, $time); //save to cache
			return $callData; //return func
		}else{ //if cache exists
			return $this->readCache($name); //read from cache
		}
	}
	public function readCache(string $name) { //read data from cache
		core::setError(); //clear error
		$config = $this->_loadConfig(); //read config
		if(!isset($config[$name])) //if cache file not exists
			return core::setError(1, 'cache not exists'); //return error
		$check = $this->__checkCache($name, $config); //check cache file
		if($check == false) //if expired
			return core::setError(2, 'cache is expired'); //return error
		$config = $config[$name]; //get config
		$path = $this->dir.$config['file']; //generate file path
		return unserialize(file_get_contents($path)); //return read file
	}
	public function writeCache(string $name, $data, int $time = 3600) : void{ //create cache
		core::setError(); //clear error
		$config = $this->_loadConfig(); //read config
		$time = time()+$time; //time
		for($i=0; $i<=1000; $i++){ //loop for generate unique file name
			$fileName = $name.'_'.$time.'_'.core::$library->string->generateString(6, [true, true, false, false]).'.cache'; //generate file name
			if(!file_exists($this->dir.$fileName)) //if not exists
				break; //end loop
		}
		$config[$name] = [
			'file' => $fileName, //file name
			'time' => $time //delete time
		]; //add/update data to config
		$this->_writeConfig($config); //file array to file
		$path = $this->dir.$fileName; //generate file path
		file_put_contents($path, serialize($data)); //save cache to file
	}
	public function deleteCache(string $name) : bool { //delete cache
		core::setError(); //clear error
		$config = $this->_loadConfig(); //read config
		if(isset($config[$name])){ //check time
			unlink($this->dir.$config[$name]['file']); //delete file
			unset($config[$name]); //delete config from file
			$this->_writeConfig($config); //save config
			return true; //return true
		}
		return false; //return false
	}
	private function _loadConfig() : array { //load config from file
		core::setError(); //clear error
		$path = $this->dir.'config.cfg'; //generate path
		if(!file_exists($path)) //if not exists
			return []; //return empty array
		return unserialize(file_get_contents($path)); //read array from file
	}
	private function _writeConfig(array $config) : void { //save config array to file
		core::setError(); //clear error
		$path = $this->dir.'config.cfg'; //generate path
		if(!file_exists($path)) //if not exists
			touch($path); //create empty file
		file_put_contents($path, serialize($config)); //save array to file
	}
	private function __checkCache(string $name, array $config = null) : bool{ //check cache file
		core::setError(); //clear error
		if($config == null) //if not config def
			$config = $this->_loadConfig(); //read config
		if(isset($config[$name]) and time() > $config[$name]['time']){ //check time
			unlink($this->dir.$config[$name]['file']); //delete file
			unset($config[$name]); //delete config from file
			$this->_writeConfig($config); //save config
			return false; //return false
		}
		return true; //return true
	}
}
?>