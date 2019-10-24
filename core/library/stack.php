<?php
return $this->stack = new class(){ 
	public $version = '1.0'; 
	public $count = 0; 
	public function create() : object{ 
		$this->count++; 
		return new class(){ 
			private $_array = array(); 
			private $_count = 0; 
			public function push(string $string) : void{ 
				$this->_array[$this->_count] = $string; 
				$this->_count++; 
			}
			public function isEmpty() : bool{ 
				return $this->_count == 0; 
			}
			public function pop(){ 
				if(!$this->isEmpty()){ 
					$this->_count--; 
					$string = $this->_array[$this->_count]; 
					unset($this->_array[$this->_count]); 
					return $string; 
				}else
					return null; 
			}
			public function count() : int{ 
				return $this->_count; 
			}
			public function clear() : void{ 
				$this->_count = 0; 
				$this->_array = array(); 
			}
		};
	}
};
?>