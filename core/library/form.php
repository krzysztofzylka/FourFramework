<?php
return $this->form = new class(){ 
	public $version = '1.0'; 
	public function list(string $method){ 
		$method = strtoupper($method); 
		if(!$this->_methodCheck($method)) 
			return core::setError(1, 'method not found'); 
		switch($method){ 
			case 'GET': 
				return array_keys($_GET); 
				break;
			case 'POST': 
				return array_keys($_POST); 
				break;
		}
	}
	public function protectAllData($method) : bool{ 
		$method = strtoupper($method); 
		if(!$this->_methodCheck($method)) 
			return core::setError(1, 'method not found'); 
		switch($method){ 
			case 'GET': 
				foreach($_GET as $key => $value) 
					$_GET[$key] = htmlspecialchars($value); 
				break;
			case 'POST': 
				foreach($_POST as $key => $value) 
					$_POST[$key] = htmlspecialchars($value); 
				break;
		}
		return true;
	}
	private function _methodCheck($name){ 
		$method_list = ['GET', 'POST']; 
		return array_search($name, $method_list)>-1?true:false; 
	}
}; 
?>