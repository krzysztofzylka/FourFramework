<?php
//główna klasa biblioteki
return $this->class = new class($this->core){
	//główna funkcja rdzenia
	protected $core;
	//główna funkcja
	public function __construct($obj){
		//inicjonowanie zmiennych
		$this->core = $obj;
	}
	//usuwanie folderu i wszystkich plików
	public function deldir($path){
		if(!file_exists($path)) return false;
		if(is_dir($dir)) {
			$objects = scandir($dir); 
			foreach ($objects as $object) { 
				if ($object != "." && $object != "..") { 
					if (is_dir($dir."/".$object)) $this->deldir($dir."/".$object);
					else unlink($dir."/".$object); 
				}
			}
			rmdir($dir); 
		}
		return true;
	}
}
?>