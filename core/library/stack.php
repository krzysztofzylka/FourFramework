<?php
return $this->stack = new class(){ //create class
	public $version = '1.0'; //version
	public $count = 0; //all stack count
	public function create() : object{ //create stack
		$this->count++; //count +1
		return new class(){ //return stack object
			private $_array = array(); //stack data
			private $_count = 0; //count element
			public function push(string $string) : void{ //push data
				$this->_array[$this->_count] = $string; //add data
				$this->_count++; //add counter
			}
			public function isEmpty() : bool{ //isEmpty
				return $this->_count == 0; //return bool
			}
			public function pop(){ //download data from stack
				if(!$this->isEmpty()){ //if not empty
					$this->_count--; //count -1
					$string = $this->_array[$this->_count]; //get string
					unset($this->_array[$this->_count]); //delete data from array
					return $string; //return string
				}else
					return null; //return null
			}
			public function count() : int{ //count stack
				return $this->_count; //return count
			}
			public function clear() : void{ //clear stack
				$this->_count = 0; //clear count
				$this->_array = array(); //clear array
			}
		};
	}
};
?>