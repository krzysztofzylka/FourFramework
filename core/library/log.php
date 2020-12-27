<?php
return $this->log = new class(){
	public $version = '1.1';
	public $writeData = '[{date}] {text}';
	private $fileName = '{year}_{month}_{day}.log';
	private $logPath = null;
	public function __construct(){
		core::setError();
		$this->logPath = core::$path['log'];
		if(!file_exists($this->logPath))
			mkdir($this->logPath, 0700, true);
		$this->setLogName($this->fileName);
	}
	public function setLogName(string $name) : void{
		core::setError();
		$this->fileName = core::$library->string->convertString($name);
		return;
	}
	public function write(string $text, string $writeData = null, string $fileName = null) : bool{
		core::setError();
		$writeData = $writeData??$this->writeData;
		$fileName = $fileName??$this->fileName;
		$write = str_replace('{date}', date('Y-m-d H:i:s'), $this->writeData);
		$write = str_replace('{text}', $text, $write);
		return file_put_contents($this->logPath.$fileName, $write.PHP_EOL, FILE_APPEND)===false?false:true;
	}
	public function logList($sort = 'fileDateTime', $sortType = 'DESC') : array{
		core::setError();
		$return = [];
		$scandir = array_diff(scandir($this->logPath), ['.', '..']);
		foreach($scandir as $fileName){
			if(core::$library->string->strpos($fileName, '.log') == -1)
				continue;
			$return[] = [
				'name' => substr($fileName, 0, strlen($fileName)-4),
				'fileName' => $fileName,
				'path' => $this->logPath.$fileName,
				'size' => filesize($this->logPath.$fileName),
				'fileTime' => filemtime($this->logPath.$fileName),
				'fileDateTime' => date("Y-m-d H:i:s", filemtime($this->logPath.$fileName))
			];
		}
		if($sort <> false)
			$return = core::$library->array->sort2D($return, $sort, $sortType);
		return $return;
	}
}
?>