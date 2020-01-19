<?php
return $this->array = new class(){ 
	public $version = '1.0a'; 
	public function trim($array){
		core::setError();
		if(!is_array($array))
			return core::setError(1, 'input is not an array');
		foreach($array as $name => $item){
			if(is_array($item))
				$array[$name] = $this->trim($item);
			else
				$array[$name] = trim($item);
		}
		return $array;
	}
	public function searchByKey($array, $keyName, $keyValue=-1){
		core::setError();
		if(!is_array($array))
			return core::setError(1, 'input is not an array');
		foreach($array as $id => $value){
			if(isset($value[$keyName])){
				if($keyValue == -1)
					return $id;
				else{
					if($value[$keyName] == $keyValue)
						return $id;
				}
			}
		}
		return -1;
	}
};
?>