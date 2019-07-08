<?php
return $this->file = new class($this->core){
	protected $core;
	public $version = '1.0.1';
	private $scanDirReplace = null;
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
		$this->core->returnError();
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
	public function scanDir(string $path, string $prevpath = null, array $array = []){
		$this->core->returnError();
		if(is_null($prevpath))
			$this->scanDirReplace = $path;
		$prevpath = $prevpath??$path;
		$scan = array_diff(scandir($path), ['.', '..']);
		foreach($scan as $name){
			$fpath = $prevpath.$name;
			array_push($array, str_replace($this->scanDirReplace, '', $fpath));
			if(is_dir($fpath))
				$array = $this->scanDir($fpath, $fpath.'/', $array);
		}
		return $array;
	}
	public function createSafeDownloadFile(string $path, string $name = null, string $contentType = null){
		if(!file_exists($path))
			return $this->core->returnError(1, 'error open file', $path);
		$name = $name??basename($path);
		$contentType = $contentType??mime_content_type($path);
		header('Content-Description: File Transfer');
		header('Content-Type: '.$contentType);
		header('Content-Disposition: attachment; filename="'.$name.'"');
		header('Expires: 0');
		header('Cache-Control: must-revalidate');
		header('Pragma: public');
		header('Content-Length: ' . filesize($path));
		ob_clean();
		flush();
		readfile($path);
		exit;
	}
}
?>