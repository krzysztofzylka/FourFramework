<?php
return $this->file = new class($this->core){
	protected $core;
	public function __construct($obj){
		$this->core = $obj;
	}
	//delete directory
	public function deldir(string $path) : bool{
		$this->core->returnError();
		if(!file_exists($path))
			return false;
		if(is_dir($dir)) {
			$objects = scandir($dir); 
			foreach ($objects as $object) { 
				if ($object != "." && $object != "..") { 
					if (is_dir($dir."/".$object))
						$this->deldir($dir."/".$object);
					else
						unlink($dir."/".$object); 
				}
			}
			rmdir($dir); 
		}
		return true;
	}
}
?>