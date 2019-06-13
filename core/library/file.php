<?php
return $this->file = new class($this->core){
	protected $core;
	public $version = '1.0';
	public function __construct($obj){
		$this->core = $obj;
	}
	public function deldir(string $path) : bool{
		$this->core->returnError();
		if(!file_exists($path))
			return false;
		if(is_dir($path)) {
			$objects = scandir($path); 
			foreach ($objects as $object) { 
				if($object != "." && $object != "..") { 
					if(is_dir($path."/".$object))
						$this->deldir($path."/".$object);
					else
						unlink($path."/".$object); 
				}
			}
			if(!@rmdir($path))
				return $this->core->returnError(1, 'error delete dir');
		}
		return true;
	}
	public function getFileUpdateDate(string $file, string $format = "Y-m-d H:i:s") {
		if(is_file($file)) {
			$filePath = $file;
			if(!realpath($filePath))
				$filePath = $_SERVER["DOCUMENT_ROOT"].$filePath;
			$fileDate = filemtime($filePath);
			if($fileDate) {
				$fileDate = date("$format",$fileDate);
				return $fileDate;
			}
			return false;
		}
		return false;
	}
}
?>