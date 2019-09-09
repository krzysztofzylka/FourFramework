<?php
return $this->form = new class(){ //create form library
	public $version = '1.0'; //version
	public function list(string $method){ //get list
		$method = strtoupper($method); //string uppercase
		if(!$this->_methodCheck($method)) //checkmethod
			return core::setError(1, 'method not found'); //return error
		switch($method){ //switch method
			case 'GET': //get
				return array_keys($_GET); //return get keys
				break;
			case 'POST': //post
				return array_keys($_POST); //return post keys
				break;
		}
	}
	public function protectAllData($method) : bool{ //protect all data in method
		$method = strtoupper($method); //string uppercase
		if(!$this->_methodCheck($method)) //checkmethod
			return core::setError(1, 'method not found'); //return error
		switch($method){ //switch method
			case 'GET': //get
				foreach($_GET as $key => $value) //foreach get data
					$_GET[$key] = htmlspecialchars($value); //protect data
				break;
			case 'POST': //post
				foreach($_POST as $key => $value) //foreach post data
					$_POST[$key] = htmlspecialchars($value); //protect data
				break;
		}
		return true;
	}
	private function _methodCheck($name){ //check method
		$method_list = ['GET', 'POST']; //avalibe method list
		return array_search($name, $method_list)>-1?true:false; //valid
	}
}; 
?>