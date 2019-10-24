<?php
return $this->global = new class(){ 
	public $version = '1.1'; 
	private $__globalVar = []; 
	public $__globalList = []; 
	public function read(string $name){ 
		core::setError(); 
		if(isset($this->__globalVar[$name])) 
			return $this->__globalVar[$name]; 
		else
			return null; 
	}
	public function write(string $name, $data) : void{ 
		core::setError(); 
		$this->__globalVar[$name] = $data; 
		array_push($this->__globalList, $name); 
		return;
	}
	public function unset(string $name) : void{ 
		core::setError(); 
		unset($this->__globalVar[$name]); 
		$this->__globalList = array_diff($this->__globalList, [$name]); 
		return;
	}
}
?>