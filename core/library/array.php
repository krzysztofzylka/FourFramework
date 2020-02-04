<?php
return $this->array = new class(){ 
	public $version = '1.1a'; 
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
	public function sort2D(array $array, string $name, string $type='ASC') : array{
		core::setError();
		$GLOBALS['module_sort2D_name'] = $name;
		switch($type){
			case 'ASC':
				usort($array, function($a, $b) {
					return $a[$GLOBALS['module_sort2D_name']] <=> $b[$GLOBALS['module_sort2D_name']];
				});
				break;
			case 'DESC':
				usort($array, function($a, $b) {
					return $a[$GLOBALS['module_sort2D_name']] <= $b[$GLOBALS['module_sort2D_name']];
				});
				break;
		}
		unset($GLOBALS['module_sort2D_name']);
		return $array;
	}
};
?>