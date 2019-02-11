<?php
return $this->class = new class($this->core){
	protected $core;
	public function __construct($obj){
		$this->core = $obj;
	}
	public function is_anonymous($class) : bool{
		$this->core->returnError();
		$search = "class@anonymous";
		if(is_object($class))
			$class = get_class((string)$class);
		if(strpos((string)$class, $search) === false)
			return false;
		return true;
	}
}
?>