<?php
class core_library_sdgh4y{
	//zmienna z rdzeniem
	protected $core;
	//lista załadowanych bibliotek
	public $__list = [];
	//główna funkcja
	public function __construct($obj){
		$this->core = $obj;
	}
	//jeżeli biblioteka nie jest zaimportowana
	public function __get($name){
		//generowanie ścieżki do biblioteki
		$path = $this->core->reversion.'core/library/'.$name.'.php';
		//jeżeli biblioteka istnieje
		if(is_file($path)){
			//dodawanie biblioteki do listy
			array_push($this->__list, $name);
			//wczytywanie biblioteki
			return include($path);
		//jeżeli biblioteka nie istnieje
		}else{
			$this->core->wlog('Error send function to \''.$name.'\' library', 'core', 'error');
			die('<b>Error send function to \''.$name.'\' library</b>');
		}
	}
};
?>