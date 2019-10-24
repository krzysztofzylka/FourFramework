<?php
return $this->api = new class(){
	public $version = '1.0';
	public function start(string $name){
		$api_pathDir = core::$path['core'].'library/api/';
		$api_path = $api_pathDir.basename($name).'.php';
		if(!file_exists($api_path))
			return core::setError(1, 'api file not exists');
		return include($api_path);
	}
}; 
?>