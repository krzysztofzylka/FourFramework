<?php
return $this->log = new class(){
	public $version = '1.0a'; 
	private $path; 
	private $fileName = '{year}_{month}_{day}.log'; 
	public $writeData = '[{date}] {text}'; 
	public function __construct(){ 
		$this->path = core::$path['log']; 
		if(!file_exists($this->path)) 
			mkdir($this->path, 0700, true); 
		$this->fileName = $this->_convertName($this->fileName); 
	}
	public function setLogPath(string $path) : void{ 
		core::setError(); 
		$this->path = $path; 
		return;
	}
	public function setLogName(string $name) : void{
		core::setError();
		$this->fileName = $this->_convertName($name);
		return;
	}
	public function _convertName(string $name) : string{ 
		core::setError(); 
		$name = str_replace('{year}', date('Y'), $name); 
		$name = str_replace('{month}', date('m'), $name); 
		$name = str_replace('{day}', date('d'), $name); 
		$name = str_replace('{hour}', date('H'), $name); 
		$name = str_replace('{min}', date('i'), $name); 
		$name = str_replace('{sec}', date('s'), $name); 
		return $name; 
	}
	public function write(string $text) : bool{ 
		core::setError(); 
		$write = str_replace('{date}', date('Y-m-d H:i:s'), $this->writeData); 
		$write = str_replace('{text}', $text, $write); 
		$return = file_put_contents($this->path.$this->fileName, $write.PHP_EOL, FILE_APPEND); 
		if($return === false)
			return false; 
		return true; 
	}
}
?>