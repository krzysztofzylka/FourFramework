<?php
return new class(){
	public function __construct(){
		if(isset($_SESSION['fdbConnect']))
			core::$library->global->write('fdbConnect', core::$library->db->connect($_SESSION['fdbConnect'][0], $_SESSION['fdbConnect'][1]));
		core::loadView('fdbeditor');
		core::setError();
	}
}
?>