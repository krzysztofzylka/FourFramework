<?php
return $this->cache = new class(){ 
	public $version = '1.1'; 
	private $dir = ''; 
	public function __construct(){ 
		core::setError(); 
		$this->dir = core::$path['base'].'cache/'; 
		if(!file_exists($this->dir)) 
			mkdir($this->dir); 
		$this->__checkAllCache();
	}
	public function funcCache(string $name, string $function, int $time = 3600){ 
		core::setError(); 
		$config = $this->_loadConfig(); 
		$check = $this->__checkCache($name, $config); 
		if(!isset($config[$name]) or $check == false){ 
			$callData = call_user_func($function); 
			$this->writeCache($name, $callData, $time); 
			return $callData; 
		}else{ 
			return $this->readCache($name); 
		}
	}
	public function readCache(string $name) { 
		core::setError(); 
		$config = $this->_loadConfig(); 
		if(!isset($config[$name])) 
			return core::setError(1, 'cache not exists'); 
		$check = $this->__checkCache($name, $config); 
		if($check == false) 
			return core::setError(2, 'cache is expired'); 
		$config = $config[$name]; 
		$path = $this->dir.$config['file']; 
		return unserialize(file_get_contents($path)); 
	}
	public function writeCache(string $name, $data, int $time = 3600) : void{ 
		core::setError(); 
		$config = $this->_loadConfig(); 
		$time = time()+$time; 
		for($i=0; $i<=1000; $i++){ 
			$fileName = $name.'_'.$time.'_'.core::$library->string->generateString(6, [true, true, false, false]).'.cache'; 
			if(!file_exists($this->dir.$fileName)) 
				break; 
		}
		$config[$name] = [
			'file' => $fileName, 
			'time' => $time 
		]; 
		$this->_writeConfig($config); 
		$path = $this->dir.$fileName; 
		file_put_contents($path, serialize($data)); 
	}
	public function deleteCache(string $name) : bool { 
		core::setError(); 
		$config = $this->_loadConfig(); 
		if(isset($config[$name])){ 
			unlink($this->dir.$config[$name]['file']); 
			unset($config[$name]); 
			$this->_writeConfig($config); 
			return true; 
		}
		return false; 
	}
	private function _loadConfig() : array { 
		core::setError(); 
		$path = $this->dir.'config.cfg'; 
		if(!file_exists($path)) 
			return []; 
		return unserialize(file_get_contents($path)); 
	}
	private function _writeConfig(array $config) : void { 
		core::setError(); 
		$path = $this->dir.'config.cfg'; 
		if(!file_exists($path)) 
			touch($path); 
		file_put_contents($path, serialize($config)); 
	}
	private function __checkCache(string $name, array $config = null) : bool{ 
		core::setError(); 
		if($config == null) 
			$config = $this->_loadConfig(); 
		if(isset($config[$name]) and time() > $config[$name]['time']){ 
			unlink($this->dir.$config[$name]['file']); 
			unset($config[$name]); 
			$this->_writeConfig($config); 
			return false; 
		}
		return true; 
	}
	private function __checkAllCache(){
		core::setError(); 
		$config = $this->_loadConfig();
		$update = false;
		$fileList = ['.', '..', 'config.cfg'];
		foreach($config as $name => $data){
			if($data['time'] < time() or !file_exists($this->dir.$data['file'])){
				if(file_exists($this->dir.$data['file']))
					unlink($this->dir.$data['file']);
				unset($config[$name]);
				$update = true;
			}
			array_push($fileList, $data['file']);
		}
		$scanDir = scanDir($this->dir);
		$scanDir = array_diff($scanDir, $fileList);
		foreach($scanDir as $fileName)
			unlink($this->dir.$fileName);
		if($update)
			$this->_writeConfig($config); 
	}
}
?>