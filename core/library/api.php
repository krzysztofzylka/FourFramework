<?php
return $this->api = new class(){ //create api library
	public $version = '1.0'; //version
	public function start(string $name){ //start api
		$api_pathDir = core::$path['core'].'library/api/'; //dir path
		$api_path = $api_pathDir.basename($name).'.php'; //file path
		if(!file_exists($api_path)) //if not exists
			return core::setError(1, 'api file not exists');
		return include($api_path);
	}
}; 
?>