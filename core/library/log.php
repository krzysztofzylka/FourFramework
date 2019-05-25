<?php
return $this->log = new class($this->core){
	protected $core;
	public $path;
	public $version = '1.0';
	public $fileName = '{year}_{month}_{day}.log';
	public $writeData = '[{date}] {text}';
	public function __construct($obj){
		$this->core = $obj;
		$this->path = $obj->path['dir_log'];
		$this->fileName = $this->_convertName($this->fileName);
	}
	public function setLogPath(string $path) : void{
		$this->path = $path;
		return;
	}
	public function setLogName(string $name) : void{
		$this->fileName = $this->_convertName($name);
		return;
	}
	private function _convertName(string $name) : string{
		$name = str_replace('{year}', date('Y'), $name);
		$name = str_replace('{month}', date('m'), $name);
		$name = str_replace('{day}', date('d'), $name);
		$name = str_replace('{hour}', date('H'), $name);
		$name = str_replace('{min}', date('i'), $name);
		$name = str_replace('{sec}', date('s'), $name);
		return $name;
	}
	public function write($text) : bool{
		$write = str_replace('{date}', date('Y-m-d H:i:s'), $this->writeData);
		$write = str_replace('{text}', $text, $write);
		$return = file_put_contents($this->path.$this->fileName, $write.PHP_EOL, FILE_APPEND);
		if($return === false) return false;
		return true;
	}
}
?>