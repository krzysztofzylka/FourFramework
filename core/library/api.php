<?php
return $this->api = new class(){
	public $version = '1.1';
	public function start(string $name){
		core::setError();
		$api_path = core::$path['library'].'/api/'.basename($name).'.php';
		if(!file_exists($api_path))
			return core::setError(1, 'api file not exists');
		return include($api_path);
	}
}; 
?>