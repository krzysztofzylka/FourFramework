<?php
return new class(){
	public $__list = []; //global data
	public function __get($name){ //get variable
		$path = core::$path['core'].'library/'.$name.'.php'; //create library file path
		if(is_file($path)){ //check file
			array_push($this->__list, $name); //add to array
			return include_once($path); //include 
		}else{ //no found
			trigger_error($name.' library not found', E_USER_ERROR); //show php error
			return core::setError(1, 'library file not found'); //return error 1
		}
	}
}
?>