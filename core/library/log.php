<?php
return $this->log = new class(){
	public $version = '1.0'; //version
	private $path; //log path
	private $fileName = '{year}_{month}_{day}.log'; //log file name
	public $writeData = '[{date}] {text}'; //data format
	public function __construct(){ //main
		$this->path = core::$path['base'].'log/'; //create path file
		if(!file_exists($this->path)) //if not exists
			mkdir($this->path, 0700, true); //create dir
		$this->fileName = $this->_convertName($this->fileName); //convert file name
	}
	public function setLogPath(string $path) : void{ //setLogPath
		core::setError(); //clear error
		$this->path = $path; //write
		return;
	}
	public function setLogName(string $name) : void{ //setLogName
		core::setError(); //clear error
		$this->fileName = $this->_convertName($name); //save to var
		return;
	}
	public function _convertName(string $name) : string{ //convert name
		core::setError(); //clear error
		$name = str_replace('{year}', date('Y'), $name); //year
		$name = str_replace('{month}', date('m'), $name); //month
		$name = str_replace('{day}', date('d'), $name); //day
		$name = str_replace('{hour}', date('H'), $name); //hour
		$name = str_replace('{min}', date('i'), $name); //min
		$name = str_replace('{sec}', date('s'), $name); //sec
		return $name; //return convert name
	}
	public function write(string $text) : bool{ //write line to log file
		core::setError(); //clear error
		$write = str_replace('{date}', date('Y-m-d H:i:s'), $this->writeData); //date
		$write = str_replace('{text}', $text, $write); //text
		$return = file_put_contents($this->path.$this->fileName, $write.PHP_EOL, FILE_APPEND); //write to file
		if($return === false)
			return false; //false
		return true; //true
	}
}
?>