<?php
return $this->global = new class(){ 
	public $version = '1.3'; 
	public $__globalVar = []; 
	public $__globalList = []; 
	public function read(string $name){ 
		core::setError(); 
		if(isset($this->__globalVar[$name])) 
			return $this->__globalVar[$name]; 
		return null; 
	}
	public function write(string $name, $data) : void{ 
		core::setError(); 
		$this->__globalVar[$name] = $data; 
		$this->_addDataToGlobalList($name);
		return;
	}
	public function unset(string $name) : void{ 
		core::setError(); 
		unset($this->__globalVar[$name]); 
		$this->__globalList = array_diff($this->__globalList, [$name]); 
		return;
	}
	public function createArray(string $name) : void{
		core::setError();
		$this->__globalVar[$name] = []; 
		$this->_addDataToGlobalList($name);
		return;
	}
	public function writeArray(string $name, string $arrayName, $data) : void{
		core::setError(); 
		if($arrayName == '')
		$this->__globalVar[$name][] = $data; 
		else
			$this->__globalVar[$name][$arrayName] = $data; 
		$this->_addDataToGlobalList($name);
		return;
	}
	private function _addDataToGlobalList($name){
		if(is_bool(array_search($name, $this->__globalList)) == true)
			array_push($this->__globalList, $name); 
	}
}
?>