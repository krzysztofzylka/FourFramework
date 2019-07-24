<?php
return $this->memory = new class(){ //create library
	public function formatBytes(int $bytes, int $prec = 2) : string{ //format bytes and add suff
		core::setError(); //clear error
		$suff = ['B', 'KB', 'MB', 'GB', 'TB', 'PT', 'EB', 'ZB', 'YB']; //table list
		$i = 0;
		while(true){
			if($bytes < 1024){
				return round($bytes, $prec).(count($suff)<=$i?'??':$suff[$i]); //return data
				break;
			}
			$bytes /= 1024;
			$i++;
		}
	}
}
?>