<?php
return $this->api = new class(){
	public $version = '1.2a';
	public $list = [];
	public function start(string $name){
		core::setError();
		$api_path = core::$path['library_api'].'/'.basename($name).'.php';
		if(!file_exists($api_path))
			return core::setError(1, 'api file not exists');
		array_push($this->list, $name);
		return include($api_path);
	}
}; 
?>