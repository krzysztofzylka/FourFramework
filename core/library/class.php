<?php
//główna klasa biblioteki
return $this->class = new class($this->core){
	//główna funkcja rdzenia
	protected $core;
	//główna funkcja
	public function __construct($obj){
		//inicjonowanie zmiennych
		$this->core = $obj;
	}
	//sprawdza czy funkcja jest anonimowa
	public function is_anonymous($class){
		//wyszukiwana treść jeżeli anonimowa
		$search = "class@anonymous";
		//jeżeli klasa to pobieranie nazwy
		if(is_object($class)) $class = get_class((string)$class);
		//jeżeli klasa anonimowa
		if(strpos((string)$class, $search) === false) return false;
		return true;
	}
}
?>