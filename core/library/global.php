<?php
return $this->global = new class($this->core){
	private $__globalVar = [];
	public $__globalList = [];
	public $version = '1.0';
	public function read($name){
		if(isset($this->__globalVar[$name]))
			return $this->__globalVar[$name];
		else
			return null;
	}
	public function write($name, $text){
		$this->__globalVar[$name] = $text;
		array_push($this->__globalList, $name);
	}
	public function unset($name) : void{
		unset($this->__globalVar[$name]);
		$this->__globalList = array_diff(__globalList, [$name]);
		return;
	}
}
?>