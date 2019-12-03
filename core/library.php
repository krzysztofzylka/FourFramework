<?php
return new class(){
	public $__list = [];
	public function __get($name){
		$path = core::$path['library'].$name.'.php';
		if(is_file($path)){
			array_push($this->__list, $name);
			return include_once($path);
		}else{
			trigger_error($name.' library not found', E_USER_ERROR);
			return core::setError(1, 'library file not found');
		}
	}
}
?>