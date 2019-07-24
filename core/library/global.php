<?php
return $this->global = new class(){ //create library
	private $__globalVar = []; //var data (private)
	public $__globalList = []; //var list
	public function read($name){ //read var
		core::setError(); //clear error
		if(isset($this->__globalVar[$name])) //search
			return $this->__globalVar[$name]; //return
		else
			return null; //not found
	}
	public function write($name, $text) : void{ //write var
		core::setError(); //clear error
		$this->__globalVar[$name] = $text; //set var
		array_push($this->__globalList, $name); //add to list
		return;
	}
	public function unset($name) : void{ //delete var
		core::setError(); //clear error
		unset($this->__globalVar[$name]); //delete
		$this->__globalList = array_diff(__globalList, [$name]); //delete from array
		return;
	}
}
?>