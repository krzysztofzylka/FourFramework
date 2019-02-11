<?php
return new class($this){
	protected $core;
	public $__list = [];
	public function __construct($obj){
		$this->core = $obj;
	}
	//if variable not exists
	public function __get($name){
		//generate path to library file
		$path = $this->core->path['dir_library'].$name.'.php';
		if(is_file($path)){
			array_push($this->__list, $name);
			return include($path);
		}else{
			$this->core->wlog('Error send function to \''.$name.'\' library', 'core', 'error');
			die('<b>Error send function to \''.$name.'\' library</b>');
		}
	}
	//debug function
	public function __debugInfo(){
		return [
			'list' => $this->__list,
		];
	}
};
?>