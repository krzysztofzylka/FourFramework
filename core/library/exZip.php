<?php
return $this->exZip = new class($this->core){
	protected $core;
	public $version = '1.0';
	public function __construct($obj){
		$this->core = $obj;
	}
	public function unzip(string $zipPath, string $extractPath){
		$zip = new ZipArchive;
		$res = $zip->open($zipPath);
		if($res === TRUE){
			$zip->extractTo($extractPath);
			$zip->close();
			return true;
		}else
			return false;
	}
}
?>