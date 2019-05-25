<?php
return $this->memory = new class($this->core){
	protected $core;
	public $version = '1.0';
	public function __construct($obj){
		$this->core = $obj;
	}
	public function formatBytes($bytes, $prec = 2) : string{
		$suff = ['B', 'KB', 'MB', 'GB', 'TB', 'PT', 'EB', 'ZB', 'YB'];
		$i = 0;
		while(true){
			if($bytes < 1024){
				return round($bytes, $prec).(count($suff)<=$i?'??':$suff[$i]);
				break;
			}
			$bytes /= 1024;
			$i++;
		}
	}
}
?>