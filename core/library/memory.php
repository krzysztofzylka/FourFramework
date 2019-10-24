<?php
return $this->memory = new class(){ 
	public $version = '1.0'; 
	public function formatBytes(int $bytes, int $prec = 2) : string{ 
		core::setError(); 
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